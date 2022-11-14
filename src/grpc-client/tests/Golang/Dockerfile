FROM golang:1.18 as builder

WORKDIR /go/builder

ADD . .
RUN go mod download
RUN GOOS=linux CGO_ENABLED=0 go build -ldflags="-s -w" -installsuffix cgo -o main main.go

FROM scratch

ENV APP_ENV=prod

COPY --from=builder /usr/share/zoneinfo/Asia/Shanghai /etc/localtime
COPY --from=builder /etc/ssl/certs/ca-certificates.crt /etc/ssl/certs/ca-certificates.crt
COPY --from=builder /go/builder /

EXPOSE 50052

ENTRYPOINT ["/main"]
