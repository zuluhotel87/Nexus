# Move this file to root /home
alias composer='docker run --rm -v $(pwd):/app composer'
alias deploy='sudo bash deploy.sh'
alias php='docker exec -it NEXUS-php php'
