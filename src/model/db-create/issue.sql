
create table issue (
    id              serial primary key,
    id_person       int not null references person(id),
    slug            varchar(255) unique,
    type            varchar(255),
    description     text
);
