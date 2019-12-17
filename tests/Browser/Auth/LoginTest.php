<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Role;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\Traits\EmailTrait;
use Illuminate\Support\Facades\Artisan;

class LoginTest extends DuskTestCase
{
    use EmailTrait;

    protected static $db_inited = false;

    public function setUp(): void
    {
        parent::setUp();

        if (!static::$db_inited) {
            static::$db_inited = true;
            $path = __DIR__ . '../../../sqlite.testing.database';
            file_put_contents($path, '');
            Artisan::call('laraone:install');
        }
    }

    public function test_user_can_login_and_logout()
    {
        $super = Role::find(1);
        $user = factory(User::class)->create([
            'activated' => true
        ]);
        $user->attachRole($super);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/auth/login')
                    ->pause(3000)
                    ->assertPathIs('/admin/content/pages')
                    ->assertSee('Pages')
                    ->assertAuthenticatedAs($user)
                    ->logout()
                    ->pause(3000)
                    ->visit('/admin/content/pages')
                    ->pause(3000)
                    ->assertPathIs('/auth/login');
        });
    }

    public function test_guest_cannot_view_admin_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/content/pages')
                    ->pause(3000)
                    ->assertPathIs('/auth/login');
        });
    }

    public function test_user_enters_wrong_password_and_fails_logging_in() {
        $this->browse(function (Browser $browser) {
            $browser->visit('/auth/login')
                    ->type('email', 'first@gmail.com')
                    ->type('password', '111111')
                    ->press('.btn-auth')
                    ->pause(3000)
                    ->visit('/admin/content/pages')
                    ->pause(3000)
                    ->assertPathIs('/auth/login');
        });
    }

    public function test_non_existing_user_fails_reseting_password() {
        $this->browse(function (Browser $browser) {
            $browser->visit('/auth/login')
                    ->click('a[href="/auth/password/reset"]')
                    ->type('email', 'example@example.com')
                    ->press('.btn-auth')
                    ->pause(3000)
                    ->assertSee('We can\'t find a user with that e-mail address.');
        });
    }

    public function test_user_successfully_resets_password() {

        $this->deleteEmails();

        $this->browse(function (Browser $browser) {
            $browser->visit('/auth/login')
                    ->click('a[href="/auth/password/reset"]')
                    ->type('email', 'admin@gmail.com')
                    ->press('.btn-auth')
                    ->pause(8000)
                    ->assertSee('An email with instructions on how to reset your password should be arriving soon. If you do not recieve the email, get in touch with us so we can help.')
                    ->visit($this->getEmail())->screenshot('email_reset')->assertPresent('.button-primary')
                    ->pause(3000);

                    // Trick to avoid _blank
                    $urlConfirm = $browser->element('.button-primary')->getAttribute('href');
                    $browser->visit($urlConfirm)->screenshot('confirmation_reset_email')->assertSee('RESET YOUR PASSWORD')

                    ->type('password', '11111111')
                    ->type('passwordConfirmation', '11111111')
                    ->press('.btn-auth')
                    ->pause(2000)
                    ->assertSee('Your password has been reset!');
        });
    }
}