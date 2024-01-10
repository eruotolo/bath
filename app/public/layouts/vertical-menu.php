<header id="page-topbar">
    <div class="navbar-header">
        <div class="d-flex">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="index.php" class="logo logo-dark">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.svg" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">Chubby</span>
                    </span>
                </a>

                <a href="index.php" class="logo logo-light">
                    <span class="logo-sm">
                        <img src="assets/images/logo-sm.svg" alt="" height="24">
                    </span>
                    <span class="logo-lg">
                        <img src="assets/images/logo-sm.svg" alt="" height="24"> <span class="logo-txt">Chubby</span>
                    </span>
                </a>
            </div>

            <button type="button" class="btn btn-sm px-3 font-size-16 header-item" id="vertical-menu-btn">
                <i class="fa fa-fw fa-bars"></i>
            </button>

        </div>

        <div class="d-flex">

            <div class="dropdown d-inline-block d-lg-none ms-2">
                <button type="button" class="btn header-item" id="page-header-search-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="search" class="icon-lg"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
                    aria-labelledby="page-header-search-dropdown">
        
                    <form class="p-3">
                        <div class="form-group m-0">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="<?php echo $language["Search"]; ?>" aria-label="Search Result">

                                <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="dropdown d-inline-block">
                <button type="button" class="btn header-item bg-light-subtle border-start border-end" id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="rounded-circle header-profile-user" src="uploads/users/<?php echo $_SESSION['image']; ?>"
                        alt="Header Avatar">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium"><?php echo $_SESSION['name']; ?> <?php echo $_SESSION['lastname']; ?>.</span>
                    <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end">
                    <!-- item-->
                    <a class="dropdown-item" href="dash-users-profile.php"><i class="mdi mdi mdi-face-man font-size-16 align-middle me-1"></i> <?php echo $language["Profile"]; ?></a>
                    <a class="dropdown-item" href="auth-lock-screen.php"><i class="mdi mdi-lock font-size-16 align-middle me-1"></i> <?php echo $language["Lock_screen"]; ?> </a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="logout.php"><i class="mdi mdi-logout font-size-16 align-middle me-1"></i> <?php echo $language["Logout"]; ?></a>
                </div>
            </div>

        </div>
    </div>
</header>

<!-- ========== Left Sidebar Start ========== -->
<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <div data-simplebar class="h-100">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu"><?php echo $language["Menu"]; ?></li>

                <li>
                    <a href="index.php">
                        <i data-feather="home"></i>
                        <span data-key="t-dashboard"><?php echo $language["Dashboard"]; ?></span>
                    </a>
                </li>

                <li class="menu-title mt-2" data-key="t-components">Contenido</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="mdi mdi-account-group-outline"></i>
                        <span data-key="t-horizontal">Clientes</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="dash-customers.php">Listado Clientes</a></li>
                        <li><a href="dash-customers-add.php">Agregar Cliente</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="mdi mdi-bathtub-outline"></i>
                        <span data-key="t-horizontal">Baños Químicos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="dash-bathrooms.php">Baños Químicos</a></li>
                        <li><a href="dash-bathrooms-add.php">Nuevo Baño Químicos</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="mdi mdi-file-sign"></i>
                        <span data-key="t-horizontal">Obra / Contratos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="dash-contracts.php">Listado Obras / Contratos</a></li>
                        <li><a href="dash-contracts-add.php">Nueva Obra / Contrato</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fas fa-tasks"></i>
                        <span data-key="t-horizontal">Seguimientos</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="dash-services.php">Listado Seguimientos</a></li>
                        <li><a href="dash-services-add.php">Nuevo Seguimiento</a></li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i class="fas fa-file-invoice"></i>
                        <span data-key="t-horizontal">Facturas</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="dash-invoices-list.php">Listado Facturas</a></li>
                        <li><a href="dash-invoices-add.php">Nueva Factura</a></li>
                    </ul>
                </li>

                <li class="menu-title mt-2" data-key="t-components">Autenticación</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="users"></i>
                        <span data-key="t-authentication"><?php echo $language["Authentication"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="dash-users-list.php">Listado de usuarios</a></li>
                        <li><a href="dash-users-add.php" data-key="t-register"><?php echo $language["Register"]; ?></a></li>
                    </ul>
                </li>

                <!-- BORRAR DESDE ACÁ -->
                <li class="menu-title mt-2" data-key="t-components"><?php echo $language["Elements"]; ?></li>


                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="file-text"></i>
                        <span data-key="t-pages"><?php echo $language["Pages"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="pages-starter.php" data-key="t-starter-page"><?php echo $language["Starter_Page"]; ?> </a></li>
                        <li><a href="pages-maintenance.php" data-key="t-maintenance"><?php echo $language["Maintenance"]; ?></a></li>
                        <li><a href="pages-comingsoon.php" data-key="t-coming-soon"><?php echo $language["Coming_Soon"]; ?></a></li>
                        <li><a href="pages-timeline.php" data-key="t-timeline"><?php echo $language["Timeline"]; ?></a></li>
                        <li><a href="pages-faqs.php" data-key="t-faqs"><?php echo $language["FAQs"]; ?></a></li>
                        <li><a href="pages-pricing.php" data-key="t-pricing"><?php echo $language["Pricing"]; ?></a></li>
                        <li><a href="pages-404.php" data-key="t-error-404"><?php echo $language["Error_404"]; ?></a></li>
                        <li><a href="pages-500.php" data-key="t-error-500"><?php echo $language["Error_500"]; ?></a></li>
                    </ul>
                </li>


                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="cpu"></i>
                        <span data-key="t-icons"><?php echo $language["Icons"]; ?></span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="icons-boxicons.php" data-key="t-boxicons"><?php echo $language["Boxicons"]; ?></a></li>
                        <li><a href="icons-materialdesign.php" data-key="t-material-design"><?php echo $language["Material_Design"]; ?></a></li>
                        <li><a href="icons-dripicons.php" data-key="t-dripicons"><?php echo $language["Dripicons"]; ?></a></li>
                        <li><a href="icons-fontawesome.php" data-key="t-font-awesome"><?php echo $language["Font_Awesome_5"]; ?></a></li>
                    </ul>
                </li>


            </ul>


        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->