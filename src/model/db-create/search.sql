
-- used for ajax search of a person by birth day or by name

create table search (
    search_term varchar(255),
    slug        varchar(255), -- person slug
    day         char(10),     -- birth day, format YYYY-MM-DD
    name        varchar(255)  -- name of the corresponding person
);
create index search_term_idx on search(search_term);
