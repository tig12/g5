
-- The structure of json fields is given by src/model/Group.yml
-- Named groop instead of group because group is a sql keyword

create table groop (
    id              serial primary key,
    slug            varchar(255) unique not null,
    name            varchar(255) unique not null,
    n               integer not null default 0,     -- nb of persons in this groop
    description     text,
    sources         jsonb,
    parents         jsonb
);
create index group_slug_idx on groop(slug);
