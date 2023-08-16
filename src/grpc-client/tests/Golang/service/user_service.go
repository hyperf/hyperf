package service

import (
	"context"
	"grpc_mock/pb/user"
)

type UserService struct {
	*user.UnimplementedUserServiceServer
}

func (u UserService) Info(ctx context.Context, id *user.UserId) (*user.UserInfo, error) {
	return &user.UserInfo{Id: id.Id, Name: "Hyperf", Gender: 1}, nil
}
