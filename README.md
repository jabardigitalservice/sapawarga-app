# Sapawarga API

[![Maintainability](https://api.codeclimate.com/v1/badges/bd503eca20b4d9ddad1e/maintainability)](https://codeclimate.com/github/jabardigitalservice/sapawarga-app/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/bd503eca20b4d9ddad1e/test_coverage)](https://codeclimate.com/github/jabardigitalservice/sapawarga-app/test_coverage)


- Program Specification on [GitBook](https://jabardigitalservice.gitbook.io/sapawarga)

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
