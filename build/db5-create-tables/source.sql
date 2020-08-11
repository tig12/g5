
-- The structure of json fields is given by src/model/Source.yml

create table source (
    id              serial primary key,
    slug            varchar(255) unique not null,
    name            varchar(255) unique not null,
    description     text,
    source          jsonb
);
create index source_slug_idx on source(slug);
                                                                                                                                            