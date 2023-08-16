package main

import (
	"google.golang.org/grpc"
	"grpc_mock/pb/user"
	"grpc_mock/service"
	"log"
	"net"
)

func main() {
	address := "0.0.0.0:50052"
	listen, err := net.Listen("tcp", address)
	if err != nil {
		log.Fatal("Failed to listen", err)
	}

	serv := grpc.NewServer()
	user.RegisterUserServiceServer(serv, &service.UserService{})

	log.Println("GRPC Server Started, listening at " + address)
	err = serv.Serve(listen)
	if err != nil {
		log.Fatal("Failed to serve", err)
	}
}
