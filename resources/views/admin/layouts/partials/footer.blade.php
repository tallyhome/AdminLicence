<footer class="footer">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </div>
            <div>
                <a href="{{ route('admin.version') }}" class="text-decoration-none">
                    Version 4.2.0
                </a>
            </div>
        </div>
    </div>
</footer>