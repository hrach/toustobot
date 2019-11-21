#!/usr/bin/env bash
if [[ $GITHUB_REF == "refs/heads/master" ]] ; then
  php app/console.php get-menu --zomato-user-key=$ZOMATO_USER_KEY --slack-url=$SLACK_URL
else
  php app/console.php get-menu --zomato-user-key=$ZOMATO_USER_KEY
fi
