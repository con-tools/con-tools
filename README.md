# ConTroll

ConTroll convention management software

## API Documentation

[Found here](docs/API.md)

## Database Model

[Found here](docs/Model.md)

## Setup

1. `git clone git@github.com:con-troll/con-troll.git`
2. `composer install`

## Deployment to Heroku

~~~
git add heroku git@heroku.com:con-troll.git
git push heroku master
~~~

After this the updated application should be available at https://con-troll.herokuapp.com/

## Database Configuration

The database setup is using a versioned database creation schema:

1. Schema changes are stored in `database/schema-X.sql` where X is the "data schema version number".
2. The database has a `system_settings` table with a record with the name `data-version` and the value is the number
of the last schema version that was applied to the database.
3. To upgrade the database, check the `data-version` field and then run all the schema files with version numbers
higher than the one recorderd. The schema files will update the `data-version` field automatically.

### Creating a new data version

1. Create a new `database/schema-X.sql` file with `X` replaced with the next higher number after the highest one 
existing.
2. Add the require DDL commands to transform the previous version to the required schema of the new version.
3. Add a DML command to set the `data-version` field to the new version number.
4. Test the database changes (see below).
5. Commit and push the changes to the source control.
6. Deploy (TODO: write automatic deployment)

### Test database changes

1. Create a new data dump from the current version of a running system (or just create a new system by running all
the previous schema files and data dump that), into `database/dumps/dump-X.sql`.
2. Edit mysql.dockerfile to reference the new file that you've just created.
3. Run `docker-compose up mysql` - this will start the database and block the current terminal. Press CTRL-C to stop 
the database.
4. In a new terminal, run `mysql -h$(docker inspect --format '{{ .NetworkSettings.IPAddress }}' controll_mysql_1) -uroot -psecret heroku_3f90e079b7e30b6 < database/schema/schema-X.sql`
5. To test again, stop the database and run `docker rm controll_mysql_1` to clear the mysql container and then start it again

## Testing the application

The application can be tested using Docker Compose. 

### Setup

1. Make sure the docker-compose command line tool is working on your system.
2. The application uses a non-default Heroku local docker setup, using PHP 7.0. To get it to run:
  1. Clone the Github repo for the docker image at https://github.com/heroku/docker-php.git
  2. Edit the `Dockerfile` and replace the `PHP_VERSION` value to `7.0.3`
  3. Build the required local image using `docker build -t heroku/php:7.0`

### Running

The docker setup includes a full Heruko stack for testing. Run `docker-compose up` to start the full web application.
When the docker web service is up, the application is accessible on port 8080 of the docker host.

In order to perform Single-Sign-On authentication (with Google/Facebook/Twitter), you'd need a `.secrets` "environment file"
with the secret client keys for those services. Either ask Oded for the keys, or generate new ones yourself by creating an
application/project in each relevant provider, and add those to the `.secrets` file like this:

    FACEBOOK_AUTH_CLIENT=APPLICATIONSECRETKEY
    GOOGLE_AUTH_CLIENT=CLIENT_ID.apps.googleusercontent.com:SECRETKEY
    TWITTER_AUTH_CLIENT=CLIENT_ID:SECRETKEY
