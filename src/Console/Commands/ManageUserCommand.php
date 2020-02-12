<?php

namespace Flashpoint\Oxidiser\Console\Commands;

use Closure;
use Flashpoint\Oxidiser\Models\Authenticator;
use Illuminate\Console\Command;

class ManageUserCommand extends Command
{
    protected $signature = 'flashpoint:user {id? : The ID of the user}';
    protected $description = 'Manage, or create a user';

    public function handle()
    {
        $user = Authenticator::query()->findOrNew($this->argument('id'));
        if ($user && $user->exists) {
            $this->info("Updating authenticator: {$user->id}/{$user->username}");
        } else {
            $this->info('Creating a new authenticator');
        }

        $this->info('Press up to select current value, leave blank to enter null value');

        $this->updateQuestion($user, 'Username', 'username');
        $this->updateQuestion($user, 'Is a User', 'is_user');
        $this->updateQuestion($user, 'Password', 'password');
        $this->updateQuestion($user, 'Permissions', 'permissions', function ($answer) {
            $answer = json_decode($answer);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $answer;
            } else {
                return null;
            }
        });
        $this->updateQuestion($user, 'Locked', 'locked');
        $user->save();
        $this->info('Done');
    }

    private function updateQuestion($user, $question, $attribute, Closure $handler = null)
    {
        $was = !is_scalar($user->$attribute) ? json_encode($user->$attribute) : $user->$attribute;

        $answer = $this->anticipate("{$question}? {$was}", [$was]);
        if (md5($answer) != md5($was)) {
            if (!empty($answer)) {
                $user->$attribute = $handler ? $handler($answer) : $answer;
            } else {
                $user->$attribute = null;
            }

            $now = !is_scalar($user->$attribute) ? json_encode($user->$attribute) : $user->$attribute;
            $this->warn("Setting {$question} to: {$now}");
        } else {
            $this->warn("{$question} unchanged");
        }
    }
}
