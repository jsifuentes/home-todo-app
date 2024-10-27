#!/bin/bash

# check if working tree has changes
if [[ -n $(git status -s) ]]; then
	echo "Working tree has changes. Please commit or stash your changes before deploying."
	exit 1
fi

# make sure we have no commits to push up
if [[ -n $(git branch --show-current) ]]; then
	echo "You have unpushed commits. Please push your commits before deploying."
	exit 1
fi

ssh user@server.lab.sifuen.com "cd ~/Documents/homelab/todo/ && git pull && docker compose stop todo && docker compose up --build -d todo"
