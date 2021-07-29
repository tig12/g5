
-- The structure of json fields is given by src/model/Occu.yml

create table occu (
    slug            varchar(255) primary key,
    wd              varchar(11) unique not null, -- varchar(9) probably enough
    name            varchar(255) unique not null, -- english
    parents         jsonb
);
