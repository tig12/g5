
-- 

create table wikiproject (
    id              serial primary key,
    slug            varchar(255) unique not null,
    name            varchar(255) unique not null,
    description     text,
    header          jsonb,
    status          varchar(255) not null
);
CREATE INDEX wikiproject_slug_idx ON wikiproject(slug);
