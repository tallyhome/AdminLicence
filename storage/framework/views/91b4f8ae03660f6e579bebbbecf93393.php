<?php use Illuminate\Support\Facades\Auth; ?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?> - <?php echo $__env->yieldContent('title'); ?></title>

    <!-- Favicon -->
    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Flag Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/css/flag-icons.min.css" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/dark-mode.css')); ?>">

    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            overflow-y: auto;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
        }
        .sidebar .nav-link:hover {
            color: rgba(255,255,255,1);
        }
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255,255,255,.1);
        }
        .content {
            padding: 20px;
        }
        .main-content-wrapper {
            margin-left: 250px;
            width: calc(100% - 250px);
        }
        /* Styles pour le sélecteur de langue */
        .navbar .nav-item.dropdown .nav-link {
            color: #333 !important;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        /* Ajout du décalage pour le sélecteur de langue */
        .navbar .nav-item:last-child {
            margin-left: 50px;
        }
        .navbar .dropdown-menu {
            min-width: 120px;
            max-width: 120px;
            padding: 0.25rem 0;
        }
        .navbar .dropdown-item {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        .navbar .dropdown-item.active {
            background-color: #f8f9fa;
            color: #333;
        }
        .navbar .dropdown-item:hover {
            background-color: #e9ecef;
        }
        /* Styles pour les drapeaux */
        .flag-icon {
            display: inline-block;
            width: 1.2em;
            height: 0.9em;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            margin-right: 0.5em;
            vertical-align: middle;
            box-shadow: 0 0 1px rgba(0,0,0,0.2);
            border-radius: 2px;
        }
        .flag-icon-fr { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/fr.svg); }
        .flag-icon-gb { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/gb.svg); }
        .flag-icon-es { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/es.svg); }
        .flag-icon-de { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/de.svg); }
        .flag-icon-it { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/it.svg); }
        .flag-icon-pt { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/pt.svg); }
        .flag-icon-nl { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/nl.svg); }
        .flag-icon-ru { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/ru.svg); }
        .flag-icon-cn { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/cn.svg); }
        .flag-icon-jp { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/jp.svg); }
        .flag-icon-tr { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/tr.svg); }
        .flag-icon-sa { background-image: url(https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/flags/4x3/sa.svg); }
        /* Styles spécifiques pour le sélecteur de langue */
        .navbar .language-selector.dropdown .nav-link {
            color: #333 !important;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            font-size: 0.875rem;
        }
        .navbar .language-selector .dropdown-menu {
            min-width: 120px;
            max-width: 120px;
            padding: 0.25rem 0;
        }
        .navbar .language-selector .dropdown-item {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            color: #333;
        }
        .navbar .language-selector .dropdown-item.active {
            background-color: #f8f9fa;
            color: #333;
        }
        .navbar .language-selector .dropdown-item:hover {
            background-color: #e9ecef;
        }

        /* Styles spécifiques pour les notifications */
        #notification-list.dropdown-menu {
            width: 400px !important;
            max-height: 600px !important;
            min-width: 400px !important;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="<?php echo e(session('dark_mode') ? 'dark-mode' : ''); ?>">
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3" style="width: 250px;">
            <div class="mb-4">
                <h4><?php echo e(config('app.name', 'Laravel')); ?></h4>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>" href="<?php echo e(route('admin.dashboard')); ?>">
                        <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                    </a>
                </li>

                <!-- Gestion des licences -->
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.projects.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.projects.index')); ?>">
                        <i class="fas fa-project-diagram me-2"></i> Projets
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.serial-keys.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.serial-keys.index')); ?>">
                        <i class="fas fa-key me-2"></i> Clés de licence
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.api-keys.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.api-keys.index')); ?>">
                        <i class="fas fa-code me-2"></i> Clés API
                    </a>
                </li>

                <!-- Gestion des emails -->
                <li class="nav-item">
                    <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#emailSubmenu">
                        <i class="fas fa-envelope me-2"></i>Email
                    </a>
                    <div class="collapse" id="emailSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.mail.settings')); ?>">
                                    <i class="fas fa-cog me-2"></i>Paramètres
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.mail.providers.phpmail.index')); ?>">
                                    <i class="fas fa-mail-bulk me-2"></i>PHPMail
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.mail.providers.mailgun.index')); ?>">
                                    <i class="fas fa-mail-bulk me-2"></i>Mailgun
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.mail.providers.mailchimp.index')); ?>">
                                    <i class="fas fa-mail-bulk me-2"></i>Mailchimp
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.mail.providers.rapidmail.index')); ?>">
                                    <i class="fas fa-mail-bulk me-2"></i>Rapidmail
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo e(route('admin.email.templates.index')); ?>">
                                    <i class="fas fa-file-alt me-2"></i>Templates
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <!-- Support -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#supportSubmenu" role="button">
                        <i class="fas fa-headset me-2"></i> Support
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.tickets.*') || request()->routeIs('admin.super.tickets.*') ? 'show' : ''); ?>" id="supportSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.tickets.*') && !request()->routeIs('admin.super.tickets.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.tickets.index')); ?>">
                                    <i class="fas fa-ticket-alt me-2"></i> Tickets
                                </a>
                            </li>
                            <?php if(auth()->guard('admin')->user()->is_super_admin): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.super.tickets.*') ? 'active' : ''); ?>" href="<?php echo e(route('admin.super.tickets.index')); ?>">
                                    <i class="fas fa-user-shield me-2"></i> Super Admin Tickets
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </li>

                <!-- Documentation -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#documentationSubmenu" role="button">
                        <i class="fas fa-book me-2"></i> Documentation
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.api.documentation') || request()->routeIs('admin.licence.documentation') ? 'show' : ''); ?>" id="documentationSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.api.documentation') ? 'active' : ''); ?>" href="<?php echo e(route('admin.api.documentation')); ?>">
                                    <i class="fas fa-code me-2"></i> Documentation API
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.licence.documentation') ? 'active' : ''); ?>" href="<?php echo e(route('admin.licence.documentation')); ?>">
                                    <i class="fas fa-key me-2"></i> Documentation des clés
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.email.documentation') ? 'active' : ''); ?>" href="<?php echo e(route('admin.email.documentation')); ?>">
                                    <i class="fas fa-envelope me-2"></i> Documentation des fournisseurs d'email
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.saas.documentation') ? 'active' : ''); ?>" href="<?php echo e(route('admin.saas.documentation')); ?>">
                                    <i class="fas fa-users me-2"></i> Documentation SaaS multiutilisateur
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request()->routeIs('admin.version') ? 'active' : ''); ?>" href="<?php echo e(route('admin.version')); ?>">
                        <i class="fas fa-code-branch me-2"></i> Informations de version
                    </a>
                </li>

                <!-- Paramètres -->
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#settingsSubmenu" role="button">
                        <i class="fas fa-cog me-2"></i> Paramètres
                    </a>
                    <div class="collapse <?php echo e(request()->routeIs('admin.settings.*') ? 'show' : ''); ?>" id="settingsSubmenu">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.settings.index') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.index')); ?>">
                                    <i class="fas fa-sliders-h me-2"></i> Général
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo e(request()->routeIs('admin.settings.two-factor') ? 'active' : ''); ?>" href="<?php echo e(route('admin.settings.two-factor')); ?>">
                                    <i class="fas fa-shield-alt me-2"></i> 2FA
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="flex-grow-1 main-content-wrapper">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-light">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <!-- Language Selector -->
                            <li class="nav-item">
                                <?php echo $__env->make('admin.layouts.partials.language-selector', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </li>
                            <!-- Composant de notifications -->
                            <li class="nav-item">
                                <?php echo $__env->make('admin.layouts.partials.notifications', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                    <?php echo e(Auth::guard('admin')->user()->name); ?>

                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form method="POST" action="<?php echo e(route('admin.logout')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Content -->
            <div class="content">
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php echo $__env->yieldContent('content'); ?>
            </div>
            
            <!-- Footer -->
            <?php echo $__env->make('admin.layouts.partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
    <script src="<?php echo e(asset('js/dark-mode.js')); ?>"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH R:\Adev\200  -  test\adminlicence\resources\views/admin/layouts/app.blade.php ENDPATH**/ ?>