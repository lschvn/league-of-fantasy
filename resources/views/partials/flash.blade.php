@if (session('success'))
    <div class="alert alert-success rounded-lg">
        <span>{{ session('success') }}</span>
    </div>
@elseif (session('error'))
    <div class="alert alert-error rounded-lg">
        <span>{{ session('error') }}</span>
    </div>
@endif
