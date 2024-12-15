#!/bin/bash

if [[ -n $(git cherry -v) ]]; then
	echo "You have unpushed commits. Please push your commits before deploying."
	exit 1
fi

DEPLOY_PATH="~/Documents/homelab/todo"

ssh-add ~/.ssh/id_*
ssh -A user@todo.lab.sifuen.com "cd $DEPLOY_PATH && git pull && docker compose stop todo && docker compose up --build -d todo"

# check if the cronjob already exists using cron -l and grep
echo "# Checking if the cronjob already exists"
if ! ssh -A user@todo.lab.sifuen.com "crontab -l | grep 'create_tasks_from_recurring'" > /dev/null 2>&1; then
	echo "# Cronjob does not exist, adding it"
	ssh -A user@todo.lab.sifuen.com "crontab -l | { cat; echo '0 * * * * bash -c \"cd $DEPLOY_PATH && docker compose exec todo php cron/create_tasks_from_recurring.php\"'; } | crontab -"
fi
