
-- The structure of json fields is given by src/model/Person.yml

create table person (
    id              serial primary key,
    slug            varchar(255) unique not null,
    sex             char(1),
    name            jsonb not null,
    occus           jsonb not null,
    birth           jsonb not null,
    death           jsonb not null,
    sources         jsonb not null,
    ids_in_sources  jsonb not null,
    trust           jsonb not null,
    acts            jsonb not null,
    history         jsonb not null,
    todo            jsonb not null,
    notes           jsonb not null
);
create index person_slug_idx on person(slug);

-- create index person_cura_gin_idx on person using gin ((ids_in_sources -> 'cura5') jsonb_path_ops);