<?php

namespace luya\envdev\envdevcmd;

use Curl\Curl;
use GitWrapper\GitWrapper;

class EnvController extends BaseCommand
{
    public $repos = [
        'luya',
        'luya-module-admin',
        'luya-module-cms',
    ];
    
    public function actionInit()
    {
        $wrapper = new GitWrapper();
        $username = $this->prompt('Whats your username?');
        $this->saveConfig('username', $username);
        
        foreach ($this->repos as $repo) {
            
            $newRepoHome = 'repos' . DIRECTORY_SEPARATOR . $repo;
            if (file_exists($newRepoHome . DIRECTORY_SEPARATOR . '.git')) {
                $this->outputSuccess("repo: \"{$repo}\" already initalize.");
                continue;
            }
            
            $hasFork = (new Curl())->get('https://github.com/'.$username.'/'.$repo.'.git')->isSuccess();
            
            if (!$hasFork) {
                $this->outputInfo("We could not find a fork {$username}/{$repo}! We can setup a read only instance, you can't change. If you want to work on this module you should clone it first!");
                
                if ($this->confirm("proceed with read only repo for {$repo}.")) {
                    $git = $wrapper->cloneRepository('git://github.com/luyadev/'.$repo.'.git', $newRepoHome);
                    $this->outputSuccess("Repo {$repo} cloned into repos");
                    $cmd = $wrapper->git('remote add upstream https://github.com/luyadev/'.$repo.'.git',  'repos' . DIRECTORY_SEPARATOR . $repo);
                    $this->outputSuccess("add remote upstream.");
                }
            }
        }
        
        return $this->outputSuccess("init complet.");
    }
    
    public function actionUpdate()
    {
        $wrapper = new GitWrapper();
        
        foreach ($this->repos as $repo) {
            $wrapper->git('checkout master',  'repos' . DIRECTORY_SEPARATOR . $repo);
        }
    }
}