
-- View used by postgrest api
create or replace view view_wikiproject_person as
select
    p.id        "person_id",
    p.slug      "person_slug",
    p.name,
    p.birth,
    wp.id       "project_id",
    wp.slug     "project_slug"
from
    person              p,
    wikiproject         wp,
    wikiproject_person
where p.id = wikiproject_person.id_person
  and wp.id = wikiproject_person.id_project
;
