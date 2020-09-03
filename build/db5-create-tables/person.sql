
-- The structure of json fields is given by src/model/Person.yml

create table person (
    id              serial primary key,
    slug            varchar(255) unique not null,
    to_check        boolean default false,
    sources         jsonb not null,
    ids_in_sources  jsonb not null,
    trust           varchar(255) not null,                                                           
    trust_details   jsonb not null,
    sex             char(1),
    name            jsonb not null,
    occus           jsonb not null,
    birth           jsonb not null,
    death           jsonb not null,
    -- admin fields
    raw             jsonb not null,
    history         jsonb not null,
    notes           jsonb
);
create index person_slug_idx on person(slug);

create index person_cura_gin_idx on person using gin ((ids_in_sources -> 'cura') jsonb_path_ops);