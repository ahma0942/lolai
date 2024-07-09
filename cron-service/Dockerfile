FROM alpine:3.20.1

RUN apk update && apk upgrade && apk add curl

COPY ./cron.d /etc/cron/crontab
RUN chmod g+s /etc/cron/crontab

COPY ./cron.sh /var/www/cron.sh
RUN chmod +x /var/www/cron.sh

RUN crontab /etc/cron/crontab

CMD ["crond", "-f"]
