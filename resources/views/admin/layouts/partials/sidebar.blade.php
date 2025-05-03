<!-- Menu de navigation principal -->
<ul class="navbar-nav">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>{{ t('common.dashboard') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.projects.index') }}">
            <i class="fas fa-project-diagram"></i>
            <span>{{ t('common.projects') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.serial-keys.index') }}">
            <i class="fas fa-key"></i>
            <span>{{ t('common.serial_keys') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.api.index') }}">
            <i class="fas fa-code"></i>
            <span>{{ t('api.api_keys') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.email.index') }}">
            <i class="fas fa-envelope"></i>
            <span>{{ t('common.email') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.support.index') }}">
            <i class="fas fa-question-circle"></i>
            <span>{{ t('layout.support') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.documentation.index') }}">
            <i class="fas fa-book"></i>
            <span>{{ t('layout.documentation') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.version.index') }}">
            <i class="fas fa-info-circle"></i>
            <span>{{ t('layout.version_info') }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('admin.settings.index') }}">
            <i class="fas fa-cog"></i>
            <span>{{ t('common.settings') }}</span>
        </a>
    </li>
</ul>