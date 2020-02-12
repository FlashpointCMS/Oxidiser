<?php

namespace Flashpoint\Oxidiser\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseMigrateMakeCommand;

class MigrateMakeCommand extends BaseMigrateMakeCommand
{
    protected function getMigrationPath()
    {
        if (!is_null($targetPath = $this->input->getOption('path'))) {
            return parent::getMigrationPath();
        }

        return $this->laravel->basePath('app/Migrations');
    }
}
