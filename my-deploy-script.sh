#!/bin/bash

if [[ -n $(git cherry -v) ]]; then
	echo "You have unpushed commits. Please push your commits before deploying."
	exit 1
fi

DEPLOY_PATH="~/Documents/homelab/todo"

ssh-add ~/.ssh/id_rsa
ssh -A user@todo.lab.sifuen.com "cd $DEPLOY_PATH && git pull && docker compose stop todo && docker compose up --build -d todo"
ssh -A user@todo.lab.sifuen.com "echo '0 * * * * bash -c \"cd $DEPLOY_PATH && docker compose exec todo php cron/create_tasks_from_recurring.php\"' > /etc/cron.d/create_tasks_from_recurring"
