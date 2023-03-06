To start the project type the following in the console:
### `php -S localhost:8070`

Routes:
- `GET constructionStages`
- `GET constructionStages/{id}`
- `POST constructionStages`
- `PATCH constructionStages/{id}`
- `DELETE constructionStages/{id}`

The API serves data and accepts payload only in JSON format.

## Validation for fields:
There is a validation system which checks every posted field against a set of rules as follows:
- `name` is maximum of 255 characters in length
- `start_date` is a valid date&time in iso8601 format i.e. `2022-12-31T14:59:00Z`
- `end_date` is either `null` or a valid datetime which is later than the `start_date`
- `duration` is automatically calculated based on `start_date`, `end_date` and `durationUnit`
- `durationUnit` is one of `HOURS`, `DAYS`, `WEEKS` or fallbacks to default value of `DAYS`
- `color` is either `null` or a valid HEX color i.e. `#FF0000`
- `externalId` is `null` or any string up to 255 characters in length
- `status` is one of `NEW`, `PLANNED` or `DELETED` and the default value is `NEW`.

## Alternate branch:
There is a seperate branch called `ValidatorExperiment` where validation was handled in a different way, which I would like to request feedback on.
