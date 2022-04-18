
-- View used by postgrest api

create or replace view api_persongroop as
select
    p.id                "person_id",
    p.slug              "person_slug",
    g.id                "group_id",
    g.slug              "group_slug",
    g.name              "group_name",
    g.type              "group_type",
    p.ids_in_sources,
    p.partial_ids,
    p.sex,
    p.name,
    p.occus,
    p.birth
from
    person p,
    groop g,
    person_groop pg
where p.id = pg.id_person
  and g.id = pg.id_groop
;
