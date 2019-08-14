FROM mysql:5.7

# Install tools
RUN apt-get update
RUN apt-get install nano -y

# Expose port
EXPOSE 3306

CMD ["mysqld", "--max_allowed_packet=128M", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci"]