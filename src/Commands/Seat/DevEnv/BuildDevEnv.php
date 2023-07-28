<?php

namespace Seat\Services\Commands\Seat\DevEnv;

use Illuminate\Console\Command;

class BuildDevEnv extends Command
{
    /** The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seat:devenv:build {--composer_json=/var/www/seat/composer.json} {--override_json=/var/www/seat/packages/override.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuilds the development environment configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $composer = $this->parse_json_file($this->option('composer_json'));
        $override = $this->parse_json_file($this->option('override_json'));

        if($composer === null || $override === null) return;

        $composer_repos = $override['repositories'] ?? [];
        $composer['repositories'] = array_map(function ($repo){
            return [
                'type'=>'path',
                'url'=>$repo['path'],
                'options'=>[
                    'symlink'=>true,
                    'versions'=>[
                        $repo['package']=>$repo['as_version']
                    ]
                ]
            ];
        }, $composer_repos);

        $new_composer = json_encode($composer,JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->info($new_composer);
        file_put_contents($this->option('composer_json'), $new_composer);
    }

    private function parse_json_file($path) : mixed
    {
        $content = file_get_contents($path);
        if($content === false){
            $this->error(sprintf('Failed to open \'%s\'.', $path));
            return null;
        }

        $data = json_decode($content, JSON_OBJECT_AS_ARRAY);
        if($data === null){
            $this->error(sprintf('Failed to parse json in \'%s\'.', $path));
            return null;
        }

        return $data;
    }
}