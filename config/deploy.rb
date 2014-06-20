set :application, "great-divide-elevation-profiler"

set :scp, :git
set :repository, "git@github.com:giegerdj/great-divide-elevation-profiler.git"

require "capistrano/ext/multistage"
set :stages, ["staging","production"]

set :default_stage, "staging"

set :ssh_options, {
    :user =>  "www-data",
    :forward_agent => true
}

set :deploy_via, :remote_cache
set :use_sudo, false

set :copy_exclude, [".git/", "Capfile", "config/"]
set :shared_children, %w()


after "deploy:create_symlink", "greatdivide:create_symlinks"
after "deploy:setup", "greatdivide:setup"

namespace :greatdivide do
    desc "Setup symlinks for the application"
    task :create_symlinks, :roles => :app do
        run "ln -nfs #{shared_path}/app/cache/ #{release_path}/app/cache/"
        run "ln -nfs #{shared_path}/www/resources/cache/ #{release_path}/www/resources/cache/"
        run "ln -nfs #{shared_path}/app/config/config-strings.php #{release_path}/app/config/config-strings.php"

    end

    desc "Create files and directories for the app's environment"
        task :setup, :roles => :app do
            set(:app_dbname, Capistrano::CLI.ui.ask("Database name: ") )
            set(:app_dbuser, Capistrano::CLI.ui.ask("Database user: ") )
            set(:app_dbpass, Capistrano::CLI.ui.ask("Database password: ") )
            set(:app_dbhost, Capistrano::CLI.ui.ask("Database host: ") )
            set(:app_secret_key, Capistrano::CLI.ui.ask("Secret Key (64+ chars): ") )
            set(:app_cache_key, Capistrano::CLI.ui.ask("Cache Key (16+ chars): ") )
            set(:app_debug, Capistrano::CLI.ui.ask("Debug mode (true/false): ") )

            db_config = ERB.new <<-EOF 
<?php
define('DB_NAME', '#{app_dbname}');
define('DB_USER', '#{app_dbuser}');
define('DB_PASSWORD', '#{app_dbpass}');
define('DB_HOST', '#{app_dbhost}');
define('SECRET_KEY', '#{app_secret_key}');
define('DEBUG', #{app_debug});
define('PROFILE_CACHE_KEY', '#{app_cache_key}');
EOF
        run "mkdir -p #{shared_path}/app/config/"
        put db_config.result, "#{shared_path}/app/config/config-strings.php"

        run "mkdir -p #{shared_path}/www/resources/cache"
        run "mkdir -p #{shared_path}/app/cache"

    end
end
