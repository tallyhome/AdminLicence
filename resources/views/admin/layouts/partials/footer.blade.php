<footer class="footer">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                &copy; {{ date('Y') }} {{ config('app.name') }}
            </div>
            <div>
                <a href="{{ route('admin.version') }}" class="text-decoration-none">
                    Version 3.3.1
                </a>
            </div>
        </div>
    </div>
</footer>