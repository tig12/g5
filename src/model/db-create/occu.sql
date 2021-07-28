
-- The structure of json fields is given by src/model/Occu.yml

create table occu (
    slug            varchar(255) primary key,
    name            varchar(255) unique not null, -- english
    wd              varchar(11), -- varchar(9) probably enough
    parents         jsonb
);
