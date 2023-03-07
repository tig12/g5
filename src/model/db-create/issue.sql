
create table issue (
    id              serial primary key,
    slug            varchar(255),
    type            varchar(255),
    description     text
);
