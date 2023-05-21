
-- The structure of json fields is given by src/model/templates/Source.yml

create table source (
    slug            varchar(255) primary key,
    name            varchar(255) unique not null,
    type            varchar(255) not null,
    authors         jsonb,
    description     text,
    parents         jsonb,
    details         jsonb
);
