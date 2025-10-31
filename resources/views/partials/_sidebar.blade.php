@php
    $dashboardActive = request()->routeIs('admin.dashboard');

    $consoleO2CActive = request()->routeIs('admin.consoles.o2c.*');
    $consoleP2PActive = request()->routeIs('admin.consoles.p2p.*');
    $consoleMTOActive = request()->routeIs('admin.consoles.mto.*');
    $consoleReplenishActive = request()->routeIs('admin.consoles.replenish.*');
    $consoleReturnsActive = request()->routeIs('admin.consoles.returns.*');
    $consoleQualityActive = request()->routeIs('admin.consoles.quality.*');
    $consoleCloseoutActive = request()->routeIs('admin.consoles.closeout.*');
    $consoleOpen = $consoleO2CActive || $consoleP2PActive || $consoleMTOActive || $consoleReplenishActive || $consoleReturnsActive || $consoleQualityActive || $consoleCloseoutActive;

    $driveActive = request()->routeIs('admin.drive.media.*');

    $marketingCustomersActive = request()->routeIs('admin.marketing.customers.*');
    $marketingOrdersActive = request()->routeIs('admin.marketing.orders.*');
    $marketingPricelistsActive = request()->routeIs('admin.marketing.pricelists.*');
    $marketingReturnsActive = request()->routeIs('admin.marketing.returns.*');
    $marketingOpen = request()->routeIs('admin.marketing.*') || $marketingCustomersActive || $marketingOrdersActive || $marketingPricelistsActive || $marketingReturnsActive;

    $procurementPosActive = request()->routeIs('admin.procurement.pos.*');
    $procurementGrnsActive = request()->routeIs('admin.procurement.grns.*');
    $procurementOpen = request()->routeIs('admin.procurement.*') || $procurementPosActive || $procurementGrnsActive;

    $inventoryHomeActive = request()->routeIs('admin.inventory.home*');
    $inventoryConsoleActive = request()->routeIs('admin.inventory.stock.console*');
    $inventoryProductsActive = request()->routeIs('admin.inventory.products.*');
    $inventoryWarehousesActive = request()->routeIs('admin.inventory.warehouses.*');
    $inventoryTransfersActive = request()->routeIs('admin.inventory.transfers.*');
    $inventoryCountsActive = request()->routeIs('admin.inventory.counts.*');
    $inventoryCategoriesActive = request()->routeIs('admin.inventory.categories.*');
    $inventorySettingsActive = request()->routeIs('admin.inventory.settings.*');
    $inventoryOpen = request()->routeIs('admin.inventory.*') || $inventoryHomeActive || $inventoryConsoleActive || $inventoryProductsActive || $inventoryWarehousesActive || $inventoryTransfersActive || $inventoryCountsActive || $inventoryCategoriesActive || $inventorySettingsActive;

    $productionWorkordersActive = request()->routeIs('admin.production.workorders.*');
    $productionBomsActive = request()->routeIs('admin.production.boms.*');
    $productionOpen = request()->routeIs('admin.production.*') || $productionWorkordersActive || $productionBomsActive;

    $hrEmployeesActive = request()->routeIs('admin.hr.employees.*');
    $hrSettingsActive = request()->routeIs('admin.hr.settings.*');
    $hrOpen = request()->routeIs('admin.hr.*') || $hrEmployeesActive || $hrSettingsActive;

    $logisticsShipmentsActive = request()->routeIs('admin.logistics.shipments.*');
    $logisticsReceiptsActive = request()->routeIs('admin.logistics.receipts.*');
    $logisticsOpen = request()->routeIs('admin.logistics.*') || $logisticsShipmentsActive || $logisticsReceiptsActive;

    $financeInvoicesActive = request()->routeIs('admin.finance.invoices.*');
    $financeReceiptsActive = request()->routeIs('admin.finance.receipts.*');
    $financeCashbookActive = request()->routeIs('admin.finance.cashbook.*');
    $financeOpen = request()->routeIs('admin.finance.*') || $financeInvoicesActive || $financeReceiptsActive || $financeCashbookActive;

    $settingsGeneralActive = request()->routeIs('admin.settings.general.*');
    $settingsEmailActive = request()->routeIs('admin.settings.email.*');
    $settingsModulesActive = request()->routeIs('admin.settings.modules.*');
    $settingsCacheActive = request()->routeIs('admin.settings.cache.*');
    $settingsDiagnosticsActive = request()->routeIs('admin.settings.diagnostics.*');
    $settingsOpen = request()->routeIs('admin.settings.*') || $settingsGeneralActive || $settingsEmailActive || $settingsModulesActive || $settingsCacheActive || $settingsDiagnosticsActive;

    $cmsEditorActive = request()->routeIs('cms.admin.editor');
    $cmsPagesActive = request()->routeIs('cms.admin.pages.*');
    $cmsMessagesActive = request()->routeIs('cms.admin.messages.*');
    $cmsOpen = request()->routeIs('cms.admin.*') || $cmsEditorActive || $cmsPagesActive || $cmsMessagesActive;
@endphp

<aside id="sidebar" class="ui-sidebar" data-ui="sidebar" data-variant="tooltip">
    <div class="ui-sidebar__inner">
        <nav class="ui-sidebar__nav" aria-label="Birincil gezinme">
            <section class="ui-sidebar__section">
                <header class="ui-sidebar__section-header">
                    <span class="ui-sidebar__section-title">Genel Bakış</span>
                    <span class="ui-sidebar__section-description">Ana paneller ve hızlı erişim</span>
                </header>

                <ul class="ui-sidebar__list">
                    <li class="ui-sidebar__item{{ $dashboardActive ? ' is-active' : '' }}">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="ui-sidebar__link"
                            aria-label="Gösterge Paneli"
                            @if($dashboardActive) aria-current="page" @endif
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-speedometer2"></i></span>
                            <span class="ui-sidebar__label">Gösterge Paneli</span>
                        </a>
                    </li>
                </ul>
            </section>

            <section class="ui-sidebar__section">
                <header class="ui-sidebar__section-header">
                    <span class="ui-sidebar__section-title">Konsollar</span>
                    <span class="ui-sidebar__section-description">Operasyonel süreç panoları</span>
                </header>

                <ul class="ui-sidebar__list">
                    <li
                        class="ui-sidebar__item has-children{{ $consoleOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-consoles"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-consoles-trigger"
                            aria-controls="sidebar-node-consoles-panel"
                            aria-expanded="{{ $consoleOpen ? 'true' : 'false' }}"
                            aria-label="Konsollar"
                            data-sidebar-target="sidebar-node-consoles-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-kanban"></i></span>
                            <span class="ui-sidebar__label">Konsol Merkezi</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-consoles-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-consoles-trigger"
                            aria-hidden="{{ $consoleOpen ? 'false' : 'true' }}"
                            @unless($consoleOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $consoleO2CActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.o2c.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleO2CActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Order to Cash</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $consoleP2PActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.p2p.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleP2PActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Procure to Pay</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $consoleMTOActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.mto.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleMTOActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Make to Order</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $consoleReplenishActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.replenish.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleReplenishActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Replenish</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $consoleReturnsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.returns.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleReturnsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Returns</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $consoleQualityActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.quality.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleQualityActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Quality</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $consoleCloseoutActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.consoles.closeout.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($consoleCloseoutActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Closeout</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="ui-sidebar__section">
                <header class="ui-sidebar__section-header">
                    <span class="ui-sidebar__section-title">İş Birimleri</span>
                    <span class="ui-sidebar__section-description">Modül bazlı uygulama rotaları</span>
                </header>

                <ul class="ui-sidebar__list">
                    <li
                        class="ui-sidebar__item has-children{{ $driveActive ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-drive"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-drive-trigger"
                            aria-controls="sidebar-node-drive-panel"
                            aria-expanded="{{ $driveActive ? 'true' : 'false' }}"
                            aria-label="Drive Merkezi"
                            data-sidebar-target="sidebar-node-drive-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-cloud-arrow-down"></i></span>
                            <span class="ui-sidebar__label">Drive Merkezi</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-drive-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-drive-trigger"
                            aria-hidden="{{ $driveActive ? 'false' : 'true' }}"
                            @unless($driveActive) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $driveActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.drive.media.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($driveActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Medya Havuzu</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $marketingOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-marketing"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-marketing-trigger"
                            aria-controls="sidebar-node-marketing-panel"
                            aria-expanded="{{ $marketingOpen ? 'true' : 'false' }}"
                            aria-label="CRM &amp; Pazarlama"
                            data-sidebar-target="sidebar-node-marketing-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-bullseye"></i></span>
                            <span class="ui-sidebar__label">CRM &amp; Pazarlama</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-marketing-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-marketing-trigger"
                            aria-hidden="{{ $marketingOpen ? 'false' : 'true' }}"
                            @unless($marketingOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $marketingCustomersActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.marketing.customers.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($marketingCustomersActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Müşteriler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $marketingOrdersActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.marketing.orders.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($marketingOrdersActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Siparişler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $marketingPricelistsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.marketing.pricelists.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($marketingPricelistsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Fiyat Listeleri</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $marketingReturnsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.marketing.returns.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($marketingReturnsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">İadeler (RMA)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $procurementOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-procurement"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-procurement-trigger"
                            aria-controls="sidebar-node-procurement-panel"
                            aria-expanded="{{ $procurementOpen ? 'true' : 'false' }}"
                            aria-label="Satın Alma"
                            data-sidebar-target="sidebar-node-procurement-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-bag-check"></i></span>
                            <span class="ui-sidebar__label">Satın Alma</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-procurement-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-procurement-trigger"
                            aria-hidden="{{ $procurementOpen ? 'false' : 'true' }}"
                            @unless($procurementOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $procurementPosActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.procurement.pos.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($procurementPosActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Satın Alma Siparişleri</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $procurementGrnsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.procurement.grns.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($procurementGrnsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Mal Kabul (GRN)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $inventoryOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-inventory"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-inventory-trigger"
                            aria-controls="sidebar-node-inventory-panel"
                            aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}"
                            aria-label="Envanter Yönetimi"
                            data-sidebar-target="sidebar-node-inventory-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                            <span class="ui-sidebar__label">Envanter Yönetimi</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-inventory-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-inventory-trigger"
                            aria-hidden="{{ $inventoryOpen ? 'false' : 'true' }}"
                            @unless($inventoryOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $inventoryHomeActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.home') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryHomeActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Kontrol Kulesi</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventoryConsoleActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.stock.console') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryConsoleActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Stok Konsolu</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventoryProductsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.products.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryProductsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Ürünler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventoryWarehousesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.warehouses.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryWarehousesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Depolar</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventoryTransfersActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.transfers.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryTransfersActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Transferler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventoryCountsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.counts.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryCountsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Sayım</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventoryCategoriesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.categories.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventoryCategoriesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Kategoriler</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $inventorySettingsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.inventory.settings.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($inventorySettingsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Ayarlar</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $productionOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-production"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-production-trigger"
                            aria-controls="sidebar-node-production-panel"
                            aria-expanded="{{ $productionOpen ? 'true' : 'false' }}"
                            aria-label="Üretim"
                            data-sidebar-target="sidebar-node-production-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-gear-wide-connected"></i></span>
                            <span class="ui-sidebar__label">Üretim</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-production-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-production-trigger"
                            aria-hidden="{{ $productionOpen ? 'false' : 'true' }}"
                            @unless($productionOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $productionWorkordersActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.production.workorders.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($productionWorkordersActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Üretim Emirleri</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $productionBomsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.production.boms.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($productionBomsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Ürün Reçeteleri</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $hrOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-hr"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-hr-trigger"
                            aria-controls="sidebar-node-hr-panel"
                            aria-expanded="{{ $hrOpen ? 'true' : 'false' }}"
                            aria-label="İK Yönetimi"
                            data-sidebar-target="sidebar-node-hr-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-people"></i></span>
                            <span class="ui-sidebar__label">İK Yönetimi</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-hr-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-hr-trigger"
                            aria-hidden="{{ $hrOpen ? 'false' : 'true' }}"
                            @unless($hrOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $hrEmployeesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.hr.employees.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($hrEmployeesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Personel Dizini</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $hrSettingsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.hr.settings.departments.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($hrSettingsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Personel Ayarları</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $logisticsOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-logistics"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-logistics-trigger"
                            aria-controls="sidebar-node-logistics-panel"
                            aria-expanded="{{ $logisticsOpen ? 'true' : 'false' }}"
                            aria-label="Lojistik"
                            data-sidebar-target="sidebar-node-logistics-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-truck"></i></span>
                            <span class="ui-sidebar__label">Lojistik</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-logistics-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-logistics-trigger"
                            aria-hidden="{{ $logisticsOpen ? 'false' : 'true' }}"
                            @unless($logisticsOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $logisticsShipmentsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.logistics.shipments.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($logisticsShipmentsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Sevkiyatlar</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $logisticsReceiptsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.logistics.receipts.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($logisticsReceiptsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Mal Kabul (GRN)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $financeOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-finance"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-finance-trigger"
                            aria-controls="sidebar-node-finance-panel"
                            aria-expanded="{{ $financeOpen ? 'true' : 'false' }}"
                            aria-label="Finans"
                            data-sidebar-target="sidebar-node-finance-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-cash-stack"></i></span>
                            <span class="ui-sidebar__label">Finans</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-finance-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-finance-trigger"
                            aria-hidden="{{ $financeOpen ? 'false' : 'true' }}"
                            @unless($financeOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $financeInvoicesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.finance.invoices.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($financeInvoicesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Faturalar</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $financeReceiptsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.finance.receipts.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($financeReceiptsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Tahsilatlar</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $financeCashbookActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.finance.cashbook.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($financeCashbookActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Cashbook (Lite)</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $settingsOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-settings"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-settings-trigger"
                            aria-controls="sidebar-node-settings-panel"
                            aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}"
                            aria-label="Ayarlar"
                            data-sidebar-target="sidebar-node-settings-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-gear"></i></span>
                            <span class="ui-sidebar__label">Ayarlar</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-settings-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-settings-trigger"
                            aria-hidden="{{ $settingsOpen ? 'false' : 'true' }}"
                            @unless($settingsOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $settingsGeneralActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.settings.general.show') }}"
                                        class="ui-sidebar__sublink"
                                        @if($settingsGeneralActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Genel Ayarlar</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $settingsEmailActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.settings.email.show') }}"
                                        class="ui-sidebar__sublink"
                                        @if($settingsEmailActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">E-posta Merkezi</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $settingsModulesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.settings.modules.show') }}"
                                        class="ui-sidebar__sublink"
                                        @if($settingsModulesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Modül Ayarları</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $settingsCacheActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.settings.cache.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($settingsCacheActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Önbellek Yönetimi</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $settingsDiagnosticsActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('admin.settings.diagnostics.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($settingsDiagnosticsActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Domain Tanılama</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li
                        class="ui-sidebar__item has-children{{ $cmsOpen ? ' is-open' : '' }}"
                        data-sidebar-collapsible="true"
                        data-sidebar-id="sidebar-node-cms"
                    >
                        <button
                            class="ui-sidebar__trigger"
                            type="button"
                            data-role="sidebar-trigger"
                            id="sidebar-node-cms-trigger"
                            aria-controls="sidebar-node-cms-panel"
                            aria-expanded="{{ $cmsOpen ? 'true' : 'false' }}"
                            aria-label="CMS Yönetimi"
                            data-sidebar-target="sidebar-node-cms-panel"
                        >
                            <span class="ui-sidebar__icon" aria-hidden="true"><i class="bi bi-browser-chrome"></i></span>
                            <span class="ui-sidebar__label">CMS Yönetimi</span>
                            <span class="ui-sidebar__caret" aria-hidden="true"><i class="bi bi-chevron-down"></i></span>
                        </button>

                        <div
                            class="ui-sidebar__panel"
                            id="sidebar-node-cms-panel"
                            data-role="sidebar-panel"
                            role="region"
                            aria-labelledby="sidebar-node-cms-trigger"
                            aria-hidden="{{ $cmsOpen ? 'false' : 'true' }}"
                            @unless($cmsOpen) hidden @endunless
                        >
                            <ul class="ui-sidebar__sublist">
                                <li class="ui-sidebar__subitem{{ $cmsEditorActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('cms.admin.editor') }}"
                                        class="ui-sidebar__sublink"
                                        @if($cmsEditorActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Canlı Editör</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $cmsPagesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('cms.admin.pages.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($cmsPagesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Sayfa Yapısı</span>
                                    </a>
                                </li>
                                <li class="ui-sidebar__subitem{{ $cmsMessagesActive ? ' is-active' : '' }}">
                                    <a
                                        href="{{ route('cms.admin.messages.index') }}"
                                        class="ui-sidebar__sublink"
                                        @if($cmsMessagesActive) aria-current="page" @endif
                                    >
                                        <span class="ui-sidebar__bullet" aria-hidden="true"></span>
                                        <span class="ui-sidebar__sublabel">Form Mesajları</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </section>
        </nav>
    </div>

    <footer class="ui-sidebar__footer" aria-label="Destek bağlantıları">
        <div class="ui-sidebar__footer-card">
            <span class="ui-sidebar__footer-eyebrow">Yardıma mı ihtiyacınız var?</span>
            <p class="ui-sidebar__footer-text">Operasyon ekibimiz destek taleplerinizi hızlıca sonuçlandırır.</p>
            <a class="ui-sidebar__footer-link" href="{{ url('admin/support/new-ticket') }}">
                <i class="bi bi-chat-dots"></i>
                <span>Destek Talebi Oluştur</span>
            </a>
        </div>
    </footer>
</aside>
