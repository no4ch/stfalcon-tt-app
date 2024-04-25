# Test task for Stfalcon from me)

## Requirements
For checking functionality you must have docker/docker-compose on your machine.

## Notes
Code has some comments about future improving. Improvements haven`t done because of time.

## How to run app
Steps:
1. Clone this repo to your `projectDir`
2. Go in `projectDir/docker`
3. Run `docker-compose up -d --build`
4. Run `docker-compose exec app cp .env.example .env`
5. Run `docker-compose exec app composer install`
6. Open 2 terminals (directory `projectDir/docker`)
7. For consuming messages in 1 terminal run `docker-compose exec app php bin/console rabbitmq:batch:consumer rolling_dice_trigger_consumer -vvv`
8. For publishing message to queue run `docker-compose exec app php bin/console app:rolling-dice-trigger`
9. Run `docker-compose exec app php bin/console app:rolling-dice-trigger` a few more times if rolling the dice is successful
10. Check logs in terminal 1. They will be about message processing and retrying
