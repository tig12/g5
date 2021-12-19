
-- The structure of json fields is given by src/model/Person.yml

create table person (
    id              serial primary key,
    slug            varchar(255) unique not null,
    ids_in_sources  jsonb not null,
    ids_partial     jsonb not null,
    name            jsonb not null,
    sex             char(1),
    birth           jsonb not null,
    death           jsonb not null,
    occus           jsonb not null,
    trust           jsonb not null,
    acts            jsonb not null,
    history         jsonb not null,
    issues          jsonb not null,
    notes           jsonb not null
);
create index person_slug_idx on person(slug);

-- create index person_lerrcp_gin_idx on person using gin ((ids_in_sources -> 'lerrcp') jsonb_path_ops);