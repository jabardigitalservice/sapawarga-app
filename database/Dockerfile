FROM mysql:5.7

# Install tools
RUN apt-get update
RUN apt-get install nano -y

# set charset and timezone
ENV LANG=C.UTF-8
ENV TZ=Asia/Jakarta
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Expose port
EXPOSE 3306

CMD ["--character-set-server=utf8mb4", \
     "--collation-server=utf8mb4_unicode_ci", \
     "--max_allowed_packet=128M", \
     "--max_execution_time=300000"]
