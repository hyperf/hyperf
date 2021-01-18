FROM node:14-alpine
WORKDIR /app
ADD . /app
RUN npm ci
CMD ["node", "route_guide_server.js", "--db_path", "./route_guide_db.json"]