
-- used for ajax search of a person by birth day or by name

create table search (
    slug        varchar(255), -- person slug
    day         char(10),     -- birth day, format YYYY-MM-DD
    name        varchar(255)  -- name of the corresponding person
);
create index search_day_idx on search(day);
create index search_slug_idx on search(slug);
