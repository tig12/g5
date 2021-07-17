
-- The structure of json fields is given by src/model/Source.yml

create table source (
    id              serial primary key,
    slug            varchar(255) unique not null,
    name            varchar(255) unique not null,
    type            varchar(255) not null,
    authors         jsonb,
    edition         varchar(255),
    isbn            varchar(13),
    description     text,
    parents         jsonb
);
create index source_slug_idx on source(slug);
