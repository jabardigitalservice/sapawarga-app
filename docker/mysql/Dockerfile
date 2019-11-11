FROM mysql:5.7

# set timezone
ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Expose port
EXPOSE 3306

CMD ["mysqld", "--max_allowed_packet=128M", "--character-set-server=utf8mb4", "--collation-server=utf8mb4_unicode_ci"]