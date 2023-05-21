
-- The structure of json fields is given by src/model/templates/Group.yml
-- Named groop instead of group because group is a sql keyword

create table groop (
    id              serial primary key,
    slug            varchar(255) unique not null,
    name            varchar(255) unique not null,
    wd              varchar(11) not null default '',    -- varchar(9) probably enough
    n               integer not null default 0,         -- nb of persons in this groop
    type            varchar(255),                       -- eg 'occu', 'history' ...
    description     text not null default '',
    download        varchar(255),                       -- optional path to file / dir to download this group
    sources         jsonb,
    parents         jsonb,                              -- group slugs
    children        jsonb                               -- group slugs
);
create index group_slug_idx on groop(slug);
