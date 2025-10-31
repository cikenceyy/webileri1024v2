<aside id="sidebar" class="ui-sidebar" data-ui="sidebar" data-variant="tooltip">
    <div class="ui-sidebar__inner">
        <nav class="ui-sidebar__nav" aria-label="Birincil gezinme">
            <section class="ui-sidebar__section">
                <header class="ui-sidebar__section-header">
                    <span class="ui-sidebar__section-title">Genel</span>
                    <span class="ui-sidebar__section-description">Sık kullanılan modüller</span>
                </header>

                <ul class="ui-sidebar__list">
                    <li class="ui-sidebar__item is-active">
                        <a href="/admin/dashboard" class="ui-sidebar__link" aria-label="Gösterge Paneli" aria-current="page">
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></span>
                            <span class="ui-sidebar__label">Gösterge Paneli</span>
                        </a>
                    </li>

                    <li
                        class="ui-sidebar__item has-children"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-sales"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-sales-trigger"
                            aria-controls="sidebar-node-sales-panel"
                            aria-expanded="false"
                            aria-label="Satış Yönetimi"
                            data-sidebar-target="sidebar-node-sales-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-basket"></i></span>
                            <span class="ui-sidebar__label">Satış Yönetimi</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-sales-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-sales-trigger"
                            aria-hidden="true"
                            hidden
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem">
                                    <a href="/admin/orders" class="ui-sidebar__sublink">
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Siparişler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem">
                                    <a href="/admin/customers" class="ui-sidebar__sublink">
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Müşteriler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem">
                                    <a href="/admin/invoices" class="ui-sidebar__sublink">
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Faturalar</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-marketing"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-marketing-trigger"
                            aria-controls="sidebar-node-marketing-panel"
                            aria-expanded="false"
                            aria-label="Pazarlama"
                            data-sidebar-target="sidebar-node-marketing-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-megaphone"></i></span>
                            <span class="ui-sidebar__label">Pazarlama</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-marketing-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-marketing-trigger"
                            aria-hidden="true"
                            hidden
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem">
                                    <a href="/admin/campaigns" class="ui-sidebar__sublink">
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Kampanyalar</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem">
                                    <a href="/admin/discounts" class="ui-sidebar__sublink">
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">İndirimler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem">
                                    <a href="/admin/banners" class="ui-sidebar__sublink">
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Banner Yönetimi</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="ui-sidebar__section">
                <header class="ui-sidebar__section-header">
                    <span class="ui-sidebar__section-title">Kaynaklar</span>
                    <span class="ui-sidebar__section-description">Takım odaklı araçlar</span>
                </header>

                <ul class="ui-sidebar__list">
                    <li class="ui-sidebar__item">
                        <a href="/admin/inventory" class="ui-sidebar__link" aria-label="Envanter">
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-boxes"></i></span>
                            <span class="ui-sidebar__label">Envanter</span>
                        </a>
                    </li>
                    <li class="ui-sidebar__item">
                        <a href="/admin/drive" class="ui-sidebar__link" aria-label="Drive">
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-cloud-arrow-down"></i></span>
                            <span class="ui-sidebar__label">Drive</span>
                        </a>
                    </li>
                    <li class="ui-sidebar__item">
                        <a href="/admin/activity" class="ui-sidebar__link" aria-label="Akış">
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-lightning-charge"></i></span>
                            <span class="ui-sidebar__label">Akış</span>
                        </a>
                    </li>
                </ul>
            </section>
        </nav>
    </div>
</aside>
