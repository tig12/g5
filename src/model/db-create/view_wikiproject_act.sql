
-- View used by postgrest api
-- Expresses the links between a wikiproject and the acts handled within this project
create or replace view view_wikiproject_act as
select
    p.id        "person_id",
    p.slug      "person_slug",
    p.name      "person_name",
    p.birth     "person_birth",
    wp.id       "project_id",
    wp.slug     "project_slug"
from
    person              p,
    wikiproject         wp,
    wikiproject_act
where p.id = wikiproject_act.id_person
  and wp.id = wikiproject_act.id_project
;
