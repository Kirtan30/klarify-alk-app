@if($domain === \App\Models\User::ALK_NO_STORE)
    <footer class="footer" style="width: 100%; text-align: center;">
        <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/pollenkontroll_logo.png')))}}"
             alt="Logo"
             style="width: 125px;"
        />
    </footer>
@elseif($domain === \App\Models\User::ALK_DK_STORE)
    <footer class="footer" style="width: 100%; text-align: center;">
        <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/pollentjek_dk_logo.png')))}}"
             alt="Logo"
             style="width: 125px;"
        />
    </footer>
@elseif($domain === \App\Models\User::KLARIFY_US_STORE)
    <footer class="footer" style="width: 100%; text-align: center;">
        <img src="{{'data:image/png;base64,' . base64_encode(file_get_contents(public_path('images/klarify.png')))}}"
             alt="Logo"
             style="width: 125px;"
        />
    </footer>
@endif
