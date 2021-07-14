# Sapawarga API

[![Maintainability](https://api.codeclimate.com/v1/badges/bd503eca20b4d9ddad1e/maintainability)](https://codeclimate.com/github/jabardigitalservice/sapawarga-app/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/bd503eca20b4d9ddad1e/test_coverage)](https://codeclimate.com/github/jabardigitalservice/sapawarga-app/test_coverage)

## Overview
- The monolith backend used by [Sapawarga Web Admin](https://gitlab.com/jdsteam/sapa-warga/sapawarga-webadmin) and [Sapawarga Mobile (Flutter)](https://gitlab.com/jdsteam/sapa-warga/sapawarga-flutter).
- Microservices version is still being developed, [hosted on GitHub](https://github.com/sapawarga). Production is currently using this monolith version.

## Technical Documentation
- Old Program Specification on [GitBook](https://jabardigitalservice.gitbook.io/sapawarga). Lists of modules, user and role permissions are still relevant, and have not been migrated to the new wiki.
- Latest Program Specification on [JDS Wiki](https://wiki.digitalservice.id/doc/5-backendapi-BKlNpyzk96).

## Quickstart for Local Development
1. Create an environment variable file named `.env-dev`, using template from `.env-template`.
    ```bash
    cp .env-template .env-dev
    ```
2. Fill the required variables.
3. Build and create Docker containers for API and database.
    ```bash
    docker-compose -f docker-compose.dev.yml up -d
    ```
4. Open web browser and go to http://localhost:81/ping. The API is ready if there are no errors and the browser displays this
    ```
    pong (1)
    ```
5. If you need to stop or remove the containers, don't forget to add option `-f docker-compose.dev.yml`. This project has two different `docker-compose.yml` files for local and staging.
    ```bash
    docker-compose -f docker-compose.dev.yml down
    ```
6. If you need to access shell of the Docker container (e.g. to run console commands), run
    ```bash
    docker-compose exec <docker_service_name> bash
    # For example, to access shell of the main app, run
    # docker-compose exec app bash
    ```
## Testing
We use [Codeception](https://codeception.com/for/yii) to write test cases. There are two kinds of tests:
1. **Unit Test:** used to test validation rules of model classes. Can also be used to test independent helper functions that don't require database connection. Test cases for unit test are located in `api/test/unit` directory. To run unit test, run

    ```bash
    docker-compose exec -T api composer run test:unit
    ```

2. **API Test:** used to test API endpoints. Test cases for API test are located in `api/test/api` directory. To run API test, run

    ```bash
    docker-compose exec -T api composer run test:api
    ```

Current CI/CD script only runs unit test because API test takes a long time to run.
## Running custom queue worker
  - Execute a single specific queue job (known `queue.id` or `queue_details.id`):

    ```bash
    yii custom-queue/run-single --queue_id=1234
    # or
    yii custom-queue/run-single --queue_details_id=1234
    ```

  - Running worker continously (similar to `yii queue/listen` command) for only a specifc type of `queue_details.type`:

    ```bash
    yii custom-queue/run-by-type <job_type> <number of job to run (default=1)> <delay in seconds (default=3)>
    ```

    notes: to make the worker run indefinitely, use limit = 0.

## Credits
Based on [chrisleekr's yii2-angular-boilerplate](https://github.com/chrisleekr/yii2-angular-boilerplate).
