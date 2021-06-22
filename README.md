# Sapawarga API

[![Maintainability](https://api.codeclimate.com/v1/badges/bd503eca20b4d9ddad1e/maintainability)](https://codeclimate.com/github/jabardigitalservice/sapawarga-app/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/bd503eca20b4d9ddad1e/test_coverage)](https://codeclimate.com/github/jabardigitalservice/sapawarga-app/test_coverage)

## Overview
- The monolith backend used by [Sapawarga Web Admin](https://gitlab.com/jdsteam/sapa-warga/sapawarga-webadmin) and [Sapawarga Mobile (Flutter)](https://gitlab.com/jdsteam/sapa-warga/sapawarga-flutter).
- Microservices version of Sapawarga is still being developed, [hosted on GitHub](https://github.com/sapawarga). Production is currently using the monolith version.

## Technical Documentation
- Old Program Specification on [GitBook](https://jabardigitalservice.gitbook.io/sapawarga). Lists of modules, user and role permissions are still relevant, and have not been migrated to the new wiki.
- Latest Program Specification on [JDS Wiki](https://wiki.digitalservice.id/doc/5-backendapi-BKlNpyzk96)

## How to use

- Running custom queue worker
  - Execute a single specifi queue job (known `queue.id` or `queue_details.id`):

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
