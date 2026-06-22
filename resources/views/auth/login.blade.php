@extends('layouts.auth', ['title' => 'Zeri HR | Sign In', 'heading' => 'Sign In'])

@section('content')
    @php($demoPassword = config('demo_users.password', 'P@ssword'))
    @php($demoAccounts = config('demo_users.accounts', []))

    <div class="login-minimal-heading">
        <h2>Welcome back</h2>
        <p>Sign in to continue.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="form">
            <div class="form-group">
                <input type="email" id="login-email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Enter email" required autofocus>
            </div>
            <div class="form-group">
                <input type="password" id="login-password" name="password" class="form-control" placeholder="Enter password" required>
            </div>
            <div class="form-group">
                <input type="checkbox" name="remember" class="form-check-input" id="remember-me" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember-me">Remember Me</label>
            </div>
            <input type="submit" class="btn btn-custom btn-fullwidth" value="Submit">
        </div>
    </form>

    <div class="login-footer">
        <a href="{{ route('password.request') }}">Forgot Password</a> | <a href="{{ route('register') }}">Register</a>
    </div>

    @if(! empty($demoAccounts))
        <div class="demo-login-panel">
            <div class="demo-login-title">Demo accounts</div>
            <div class="demo-login-grid">
                @foreach($demoAccounts as $account)
                    <button
                        type="button"
                        class="demo-copy-btn"
                        data-email="{{ $account['email'] }}"
                        data-password="{{ $demoPassword }}"
                    >
                        <span>{{ $account['label'] }}</span>
                        <small>Copy</small>
                    </button>
                @endforeach
            </div>
            <div class="demo-login-note">Default password: <code>{{ $demoPassword }}</code></div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .wrapper-login {
            background: #f5f7fa;
            padding: 24px 14px;
        }

        .login-inner {
            width: min(100%, 390px);
        }

        .auth-logo {
            margin-bottom: 14px;
        }

        .auth-logo img {
            width: 132px;
        }

        .wrapper-login .card.mb-1 {
            display: none;
        }

        .form-wrapper {
            min-height: 0;
            padding: 24px;
            border: 1px solid #e0e8ef;
            border-radius: 8px;
            box-shadow: 0 12px 34px rgba(18, 48, 74, 0.08);
            background: #fff;
        }

        .login-minimal-heading {
            margin-bottom: 18px;
            text-align: left;
        }

        .login-minimal-heading h2 {
            margin: 0;
            color: #1f3349;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .login-minimal-heading p {
            margin: 5px 0 0;
            color: #65788b;
            font-size: 13px;
        }

        .wrapper-login .form-control {
            height: 44px;
            border-color: #d5e1ea;
            border-radius: 6px;
            font-size: 14px;
        }

        .wrapper-login .btn-fullwidth {
            height: 44px;
            border-radius: 6px;
            font-weight: 700;
        }

        .login-footer {
            justify-content: center;
            padding-top: 16px;
            font-size: 13px;
        }

        .demo-login-panel {
            margin-top: 16px;
            padding-top: 14px;
            border: 1px solid #d9e4ec;
            border-width: 1px 0 0;
            background: transparent;
        }

        .demo-login-title {
            font-weight: 700;
            color: #1f3349;
            margin-bottom: 8px;
            font-size: 13px;
        }

        .demo-login-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 6px;
        }

        .demo-copy-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
            min-height: 36px;
            border: 1px solid #dce7ef;
            border-radius: 5px;
            padding: 7px 8px;
            background: #fff;
            color: #24374a;
            font-weight: 700;
            font-size: 12px;
            text-align: left;
        }

        .demo-copy-btn small {
            color: #319795;
            font-size: 11px;
            font-weight: 700;
        }

        .demo-login-note {
            margin-top: 8px;
            color: #65788b;
            font-size: 12px;
        }

        @media (max-width: 420px) {
            .form-wrapper {
                padding: 20px;
            }

            .demo-login-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.demo-copy-btn').forEach(function (button) {
            button.addEventListener('click', function () {
                var email = button.getAttribute('data-email');
                var password = button.getAttribute('data-password');
                var emailInput = document.getElementById('login-email');
                var passwordInput = document.getElementById('login-password');

                if (emailInput) {
                    emailInput.value = email;
                }

                if (passwordInput) {
                    passwordInput.value = password;
                }

                var text = 'Email: ' + email + '\nPassword: ' + password;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text);
                }

                var label = button.querySelector('small');
                if (label) {
                    label.textContent = 'Copied';
                }

                setTimeout(function () {
                    if (label) {
                        label.textContent = 'Copy';
                    }
                }, 1400);
            });
        });
    </script>
@endpush
