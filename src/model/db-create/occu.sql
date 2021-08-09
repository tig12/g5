
-- Occupations

create table occu (
    slug            varchar(255) primary key,
    wd              varchar(11) not null default '',    -- varchar(9) probably enough
    name            varchar(255) unique not null,       -- english
    n               integer not null default 0,         -- nb of persons with this occupation code
    parents         jsonb                               -- list of urls
);
