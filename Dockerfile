FROM heroku/php

RUN chmod a+rwX /app/user/application/cache /app/user/application/logs
