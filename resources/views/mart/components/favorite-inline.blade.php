@auth
    <a class="custom-link favorite" lotId="{{ $lot->id }}">
        @if(Auth::user()->getFavoriteAttribute($lot->id) == false)
            <span id="favoriteStatus-{{ $lot->id }}" class="google-icon">
                <span class="material-symbols-outlined uk-text-middle">favorite</span>
             </span>
        @else
            <span id="favoriteStatus-{{ $lot->id }}" class="google-icon-fill">
                 <span class="material-symbols-outlined uk-text-middle">favorite</span>
            </span>
        @endif
    </a>
@endauth
@guest
    <a class="custom-link un-login-favorite">
        <span id="favoriteStatus" class="google-icon">
            <span class="material-symbols-outlined uk-text-middle">favorite</span>
        </span>
    </a>
@endguest
