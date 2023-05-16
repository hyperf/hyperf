CREATE TABLE users
(
    id    SERIAL
        constraint users_id
            primary key,
    email varchar(255) not null
        constraint users_email
            unique,
    name  varchar(255) not null
);

INSERT INTO public.users (id, email, name) VALUES (DEFAULT, 'test@hyperf.io', 'hyperf');
