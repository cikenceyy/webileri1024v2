(function () {
    window.Inventory = window.Inventory || {};

    const Settings = {
        selectors: {
            host: '.inv-settings',
            tabs: '[data-settings-tab]',
            treeNode: '[data-settings-node]',
            bulkAction: '[data-action="settings-bulk"]',
        },

        init() {
            this.cache();
            if (!this.$host) {
                return;
            }

            this.bind();
        },

        cache() {
            this.$host = document.querySelector(this.selectors.host);
            if (!this.$host) {
                return;
            }

            this.$detail = this.$host.querySelector('[data-detail-region]');
        },

        bind() {
            this.$host.addEventListener('click', (event) => {
                const tab = event.target.closest(this.selectors.tabs);
                if (tab) {
                    event.preventDefault();
                    this.switchTab(tab.dataset.settingsTab);
                    return;
                }

                const node = event.target.closest(this.selectors.treeNode);
                if (node) {
                    event.preventDefault();
                    this.selectNode(node);
                    return;
                }

                const bulk = event.target.closest(this.selectors.bulkAction);
                if (bulk) {
                    event.preventDefault();
                    this.runBulkAction(bulk.dataset.actionType);
                }
            });
        },

        switchTab(tab) {
            if (!tab) {
                return;
            }

            this.$host.dataset.activeTab = tab;
            this.$host.dispatchEvent(new CustomEvent('inventory:settings:tab', { detail: { tab } }));
        },

        selectNode(node) {
            this.$host.querySelectorAll(this.selectors.treeNode).forEach((item) => item.dataset.active = 'false');
            node.dataset.active = 'true';

            const payload = {
                id: node.dataset.id,
                type: node.dataset.type,
            };

            this.$host.dispatchEvent(new CustomEvent('inventory:settings:node', { detail: payload }));
            this.loadDetail(payload);
        },

        runBulkAction(actionType) {
            if (!actionType) {
                return;
            }

            const event = new CustomEvent('inventory:settings:bulk', { detail: { actionType } });
            this.$host.dispatchEvent(event);
        },

        loadDetail(payload) {
            if (!this.$detail) {
                return;
            }

            const endpoint = this.$detail.dataset.endpoint;
            if (!endpoint) {
                return;
            }

            const url = new URL(endpoint, window.location.origin);
            url.searchParams.set('id', payload.id);
            url.searchParams.set('type', payload.type);

            fetch(url)
                .then((response) => response.ok ? response.text() : '')
                .then((html) => {
                    this.$detail.innerHTML = html;
                })
                .catch(() => {});
        },
    };

    window.Inventory.Settings = Settings;

    document.addEventListener('DOMContentLoaded', () => Settings.init());
})();
