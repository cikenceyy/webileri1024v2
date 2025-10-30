/**
 * Rapor kartlarını JSON listesiyle eşitleyip yenileme butonlarını yönetir.
 */
class ReportCenter {
    constructor(root) {
        this.root = root;
        this.listEndpoint = root.dataset.listEndpoint;
        this.refreshTemplate = root.dataset.refreshEndpoint;
        this.downloadTemplate = root.dataset.downloadEndpoint;
        this.cards = new Map();
        this.etag = null;
        this.pollInterval = Number.parseInt(root.dataset.pollInterval ?? '60', 10) * 1000;

        this.attachCards();
        this.load();
        this.startPolling();
    }

    attachCards() {
        this.root.querySelectorAll('[data-report-card]').forEach((card) => {
            const key = card.dataset.reportKey;
            if (!key) {
                return;
            }
            this.cards.set(key, {
                element: card,
                updated: card.querySelector('[data-report-updated]'),
                valid: card.querySelector('[data-report-valid]'),
                rows: card.querySelector('[data-report-rows]'),
                status: card.querySelector('[data-report-status]'),
                download: card.querySelector('[data-report-download]'),
                refresh: card.querySelector('[data-report-refresh]'),
            });
        });

        this.cards.forEach((card, key) => {
            card.refresh?.addEventListener('click', () => this.triggerRefresh(key, card));
        });
    }

    async load(force = false) {
        if (!this.listEndpoint) {
            return;
        }

        try {
            const headers = { 'X-Requested-With': 'XMLHttpRequest' };
            if (this.etag && !force) {
                headers['If-None-Match'] = this.etag;
            }
            const response = await fetch(this.listEndpoint, { headers });
            if (response.status === 304) {
                return;
            }
            if (!response.ok) {
                throw new Error('Rapor listesi alınamadı');
            }
            this.etag = response.headers.get('ETag');
            const payload = await response.json();
            this.updateCards(payload.snapshots ?? []);
        } catch (error) {
            console.error(error);
        }
    }

    updateCards(snapshots) {
        snapshots.forEach((snapshot) => {
            const card = this.cards.get(snapshot.report_key);
            if (!card) {
                return;
            }
            if (card.updated) {
                card.updated.textContent = snapshot.generated_at ? new Date(snapshot.generated_at).toLocaleString('tr-TR') : 'hazırlanmadı';
            }
            if (card.valid) {
                card.valid.textContent = snapshot.valid_until ? new Date(snapshot.valid_until).toLocaleString('tr-TR') : '—';
            }
            if (card.rows) {
                card.rows.textContent = snapshot.rows ?? 0;
            }
            if (card.status) {
                card.status.textContent = snapshot.status ?? 'pending';
            }
            if (card.download) {
                if (snapshot.storage_path) {
                    card.download.classList.remove('disabled');
                    card.download.removeAttribute('aria-disabled');
                    card.download.href = this.buildDownloadUrl(snapshot.id);
                } else {
                    card.download.classList.add('disabled');
                    card.download.setAttribute('aria-disabled', 'true');
                    card.download.removeAttribute('href');
                }
            }
        });
    }

    buildDownloadUrl(snapshotId) {
        if (!this.downloadTemplate) {
            return '#';
        }

        return this.downloadTemplate.replace('__ID__', encodeURIComponent(snapshotId));
    }

    async triggerRefresh(key, card) {
        if (!this.refreshTemplate) {
            return;
        }

        const url = this.refreshTemplate.replace('__KEY__', encodeURIComponent(key));
        const button = card.refresh;
        const original = button?.textContent;
        if (button) {
            button.disabled = true;
            button.textContent = button.dataset.reportLoadingText ?? 'Yenileniyor…';
        }

        try {
            const tokenEl = document.querySelector('meta[name="csrf-token"]');
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': tokenEl?.getAttribute('content') ?? '',
                },
            });
            if (!response.ok) {
                throw new Error('Yenileme başlatılamadı');
            }
            this.toast('Rapor yenileme kuyruğa alındı.');
            this.load(true);
        } catch (error) {
            console.error(error);
            this.toast('Rapor yenileme sırasında hata oluştu.', 'danger');
        } finally {
            if (button) {
                button.disabled = false;
                button.textContent = original;
            }
        }
    }

    startPolling() {
        if (Number.isNaN(this.pollInterval) || this.pollInterval <= 0) {
            return;
        }

        window.setInterval(() => this.load(), this.pollInterval);
    }

    toast(message, type = 'success') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;
        alert.style.position = 'fixed';
        alert.style.bottom = '1.5rem';
        alert.style.right = '1.5rem';
        alert.style.zIndex = '1060';
        document.body.appendChild(alert);
        setTimeout(() => alert.remove(), 4000);
    }
}

function bootstrapReports() {
    const root = document.querySelector('[data-report-center]');
    if (!root) {
        return;
    }
    new ReportCenter(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootstrapReports);
} else {
    bootstrapReports();
}
