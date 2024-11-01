#!/bin/bash

if [[ -n $(git cherry -v) ]]; then
	echo "You have unpushed commits. Please push your commits before deploying."
	exit 1
fi

ssh-add ~/.ssh/id_rsa
ssh -A user@todo.lab.sifuen.com "cd ~/Documents/homelab/todo/ && git pull && docker compose stop todo && docker compose up --build -d todo"
