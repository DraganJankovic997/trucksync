FROM postgres:16-alpine

COPY docker/postgres/start.sh /usr/local/bin/trucksync-postgres-start
RUN chmod +x /usr/local/bin/trucksync-postgres-start

ENTRYPOINT ["trucksync-postgres-start"]
CMD ["postgres"]
