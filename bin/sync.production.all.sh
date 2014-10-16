# syncing controllers
rsync --delete -avze ssh app/routes.php app/controllers app/models app/views yatimesheet.de@ssh.yatimesheet.de:/www/app/
