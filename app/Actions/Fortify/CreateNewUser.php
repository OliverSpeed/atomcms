<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\WebsiteBetaCode;
use App\Providers\RouteServiceProvider;
use App\Rules\BetaCodeRule;
use App\Rules\GoogleRecaptchaRule;
use App\Actions\Fortify\Rules\PasswordValidationRules;
use App\Rules\WebsiteWordfilterRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     */
    public function create(array $input)
    {
        // if registration is disabled, throw an exception
        if (setting('disable_register')) {
            throw ValidationException::withMessages([
                'registration' => __('Registration is disabled.'),
            ]);
        }

        $ip = request()->ip();
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            throw ValidationException::withMessages([
                'registration' => __('Your IP address seems to be invalid'),
            ]);
        }

        $matchingIpCount = User::query()
            ->where('ip_last', '=', $ip)
            ->orWhere('ip_reg', '=', $ip)
            ->count();

        if ($matchingIpCount >= (int)setting('max_accounts_per_ip')) {
            throw ValidationException::withMessages([
                'registration' => __('You have reached the max amount of allowed account'),
            ]);
        }

        $this->validate($input);

        $user = User::create([
            'username' => $input['username'],
            'mail' => $input['mail'],
            'password' => Hash::make($input['password']),
            'account_created' => time(),
            'last_online' => time(),
            'motto' => setting('start_motto'),
            'look' => setting('start_look'),
            'credits' => setting('start_credits'),
            'ip_reg' => $ip,
            'ip_last' => $ip,
            'auth_ticket' => '',
            'home_room' => (int)setting('hotel_home_room'),
        ]);

        $user->update([
            'referral_code' => sprintf('%s%s', $user->id, Str::random(5))
        ]);

        if (setting('requires_beta_code')) {
            WebsiteBetaCode::where('code', '=', $input['beta_code'])->update([
                'user_id' => $user->id
            ]);
        }

        // Referral
        if ($input['referral_code']) {
            $referralUser = User::query()
                ->where('referral_code', '=', $input['referral_code'])
                ->first();

            if (is_null($referralUser)) {
                return redirect(RouteServiceProvider::HOME);
            }

            // If same IP skip referral incrementation
            if ($referralUser->ip_last == $user->ip_last || $referralUser->ip_reg == $user->ip_reg) {
                return redirect(RouteServiceProvider::HOME);
            }

            $referralUser->referrals()->updateOrCreate(['user_id' => $referralUser->id], [
                'referrals_total' => $referralUser->referrals != null ? $referralUser->referrals->referrals_total += 1 : 1,
            ]);

            $referralUser->userReferrals()->create([
                'referred_user_id' => $user->id,
                'referred_user_ip' => $ip,
            ]);
        }

        return $user;
    }

    private function validate(array $inputs): array
    {
        $rules = [
            'username' => ['required', 'string', sprintf('regex:%s', setting('username_regex')), 'max:25', Rule::unique('users'), new WebsiteWordfilterRule],
            'mail' => ['required', 'string', 'email', 'max:255', Rule::unique('users')],
            'password' => $this->passwordRules(),
            'beta_code' => ['sometimes', 'string', new BetaCodeRule],
            'terms' => ['required', 'accepted'],
            'g-recaptcha-response' => ['sometimes', 'string', new GoogleRecaptchaRule()],
        ];

        $messages =  [
            'g-recaptcha-response.required' => __('The Google recaptcha must be completed'),
            'g-recaptcha-response.string' => __('The google recaptcha was submitted with an invalid type'),
        ];

        return Validator::make($inputs, $rules, $messages)->validate();
    }
}
