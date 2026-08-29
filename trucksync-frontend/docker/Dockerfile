FROM node:24-alpine

WORKDIR /app

COPY package*.json ./
RUN npm ci --ignore-scripts

COPY . .
RUN npm run postinstall

EXPOSE 9000

CMD ["npm", "run", "dev", "--", "--hostname", "0.0.0.0", "--port", "9000"]
