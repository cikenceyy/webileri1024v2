@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toSnakeCase = (value) => value
                .replace(/([A-Z])/g, '_$1')
                .replace(/__+/g, '_')
                .replace(/^_+|_+$/g, '')
                .toLowerCase();

            document.querySelectorAll('[data-console-root]').forEach((root) => {
                const stepper = root.querySelectorAll('[data-step-target]');
                const lists = root.querySelectorAll('[data-step-list]');
                const footerCount = root.querySelector('[data-console-selected-count]');
                const footerAmount = root.querySelector('[data-console-total-amount]');
                const actionForm = root.querySelector('[data-console-action-form]');
                const selectedContainer = root.querySelector('[data-console-selected-inputs]');
                const warehouseInput = root.querySelector('[data-console-warehouse]');
                const defaultAction = root.dataset.defaultAction || '';
                const printAction = root.dataset.printAction || '';
                const reasonInput = root.querySelector('[data-console-reason]');
                const selectionMode = root.dataset.selectionMode || 'ids';
                let actionInput = null;
                if (actionForm) {
                    actionInput = actionForm.querySelector('[data-console-action-input]')
                        || actionForm.querySelector('input[name="action"]');
                }

                let currentStep = stepper[0]?.dataset.stepTarget;

                const refreshFooter = () => {
                    const checked = root.querySelectorAll('[data-console-checkbox]:checked');
                    if (footerCount) {
                        footerCount.textContent = checked.length.toString();
                    }
                    if (!footerAmount) {
                        return;
                    }
                    let total = 0;
                    checked.forEach((checkbox) => {
                        const row = checkbox.closest('[data-console-row]');
                        if (!row) return;
                        const amount = parseFloat(row.dataset.amount || '0');
                        if (!Number.isNaN(amount)) {
                            total += amount;
                        }
                    });
                    footerAmount.textContent = new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(total);
                };

                const syncSelection = () => {
                    if (!selectedContainer) {
                        return;
                    }
                    selectedContainer.innerHTML = '';
                    const checked = root.querySelectorAll('[data-console-checkbox]:checked');
                    checked.forEach((checkbox, index) => {
                        const row = checkbox.closest('[data-console-row]');
                        if (!row) {
                            return;
                        }
                        if (selectionMode === 'lines') {
                            const productId = row.dataset.productId;
                            const qtyInput = row.querySelector('[data-console-qty]');
                            const qty = qtyInput ? qtyInput.value : row.dataset.amount;
                            if (!productId) {
                                return;
                            }
                            const productField = document.createElement('input');
                            productField.type = 'hidden';
                            productField.name = `lines[${index}][product_id]`;
                            productField.value = productId;
                            selectedContainer.appendChild(productField);

                            const qtyField = document.createElement('input');
                            qtyField.type = 'hidden';
                            qtyField.name = `lines[${index}][qty]`;
                            qtyField.value = qty || 0;
                            selectedContainer.appendChild(qtyField);
                        } else if (selectionMode === 'objects') {
                            const entries = Object.entries(row.dataset)
                                .filter(([key]) => key.startsWith('selection'));

                            if (!entries.length) {
                                return;
                            }

                            entries.forEach(([key, value]) => {
                                const field = toSnakeCase(key.replace(/^selection/, ''));
                                if (!field) {
                                    return;
                                }
                                const objectField = document.createElement('input');
                                objectField.type = 'hidden';
                                objectField.name = `selection[${index}][${field}]`;
                                objectField.value = value ?? '';
                                selectedContainer.appendChild(objectField);
                            });
                        } else {
                            const hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'ids[]';
                            hidden.value = checkbox.value;
                            selectedContainer.appendChild(hidden);
                        }
                    });
                    refreshFooter();
                };

                stepper.forEach((button) => {
                    button.addEventListener('click', () => {
                        const target = button.dataset.stepTarget;
                        currentStep = target;
                        stepper.forEach((btn) => btn.classList.toggle('active', btn === button));
                        lists.forEach((list) => list.classList.toggle('d-none', list.dataset.stepList !== target));
                    });
                });

                root.querySelectorAll('[data-console-select-all]').forEach((toggle) => {
                    toggle.addEventListener('change', (event) => {
                        const step = toggle.getAttribute('data-console-select-all');
                        root.querySelectorAll(`[data-console-row][data-console-step="${step}"] [data-console-checkbox]`).forEach((checkbox) => {
                            checkbox.checked = event.target.checked;
                        });
                        syncSelection();
                    });
                });

                root.querySelectorAll('[data-console-checkbox]').forEach((checkbox) => {
                    checkbox.addEventListener('change', syncSelection);
                });

                root.querySelectorAll('[data-console-action]').forEach((button) => {
                    button.addEventListener('click', () => {
                        if (!actionForm) {
                            return;
                        }
                        const action = button.getAttribute('data-console-action');
                        if (actionInput) {
                            actionInput.value = action;
                        }
                        const warehouseField = actionForm.querySelector('input[name="warehouse_id"]');
                        if (warehouseField) {
                            warehouseField.value = warehouseInput?.value || '';
                        }
                        const reasonField = actionForm.querySelector('input[name="reason"]');
                        if (reasonField) {
                            reasonField.value = reasonInput?.value || '';
                        }
                        const notesField = actionForm.querySelector('[data-console-notes-input]');
                        if (notesField) {
                            notesField.value = reasonInput?.value || '';
                        }
                        if (!selectedContainer || !selectedContainer.children.length) {
                            alert('{{ __('Önce en az bir kayıt seçin.') }}');
                            return;
                        }
                        actionForm.submit();
                    });
                });

                document.addEventListener('keydown', (event) => {
                    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
                        return;
                    }
                    if (event.key === '/') {
                        const search = root.querySelector('input[type="search"], input[name="search"]');
                        if (search) {
                            event.preventDefault();
                            search.focus();
                        }
                    }
                    if (event.key.toLowerCase() === 'a') {
                        const list = root.querySelector(`[data-step-list="${currentStep}"]`);
                        if (!list) return;
                        list.querySelectorAll('[data-console-checkbox]').forEach((checkbox) => {
                            checkbox.checked = true;
                        });
                        syncSelection();
                    }
                    if (event.key === 'Enter' && defaultAction) {
                        event.preventDefault();
                        const button = root.querySelector(`[data-console-action="${defaultAction}"]`);
                        button?.click();
                    }
                    if (event.key.toLowerCase() === 'p' && printAction) {
                        event.preventDefault();
                        const button = root.querySelector(`[data-console-action="${printAction}"]`);
                        button?.click();
                    }
                });
            });
        });
    </script>
@endpush
