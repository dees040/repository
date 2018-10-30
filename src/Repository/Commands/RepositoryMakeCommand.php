<?php

namespace Dees040\Repository\Commands;

use Illuminate\Console\GeneratorCommand;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Indicates which stub to use.
     *
     * @var int
     */
    protected $stub = 1;

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->stub === 1
            ? __DIR__.'/stubs/contract.stub'
            : __DIR__.'/stubs/repository.stub';
    }

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $success = parent::handle();

        if ($success === false) {
            return $success;
        }

        $this->stub = 2;

        parent::handle();
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput()
    {
        $name = trim($this->argument('name'));

        return $this->stub === 1
            ? sprintf('%sRepository', $name)
            : sprintf('%sEloquentRepository', $name);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $stub = parent::replaceClass($stub, $name);

        if ($this->stub === 1) {
            return $stub;
        }

        $name = sprintf('%sRepository', $this->argument('name'));

        return str_replace('DummyRepository', $name, $stub);
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $this->stub === 1
            ? $rootNamespace.'\Repositories\Contracts'
            : $rootNamespace.'\Repositories';
    }
}
