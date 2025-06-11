--
-- PostgreSQL database dump
--

-- Dumped from database version 17.4
-- Dumped by pg_dump version 17.4

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: anecdote; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.anecdote (
    id_ann integer NOT NULL,
    nom character varying(50) NOT NULL,
    text text NOT NULL,
    date_ date NOT NULL,
    tags character varying(50) NOT NULL,
    text_eng text,
    titre_eng character varying(50),
    tags_eng character varying(50)
);


ALTER TABLE public.anecdote OWNER TO postgres;

--
-- Name: TABLE anecdote; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.anecdote IS 'Table des anecdotes';


--
-- Name: COLUMN anecdote.id_ann; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.anecdote.id_ann IS 'Identifiant unique de l''anecdote';


--
-- Name: COLUMN anecdote.text; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.anecdote.text IS 'Contenu textuel de l''anecdote';


--
-- Name: COLUMN anecdote.date_; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.anecdote.date_ IS 'Date de l''anecdote';


--
-- Name: COLUMN anecdote.tags; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.anecdote.tags IS 'Tags associÃ©s Ã  l''anecdote';


--
-- Name: anecdote_id_ann_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.anecdote_id_ann_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.anecdote_id_ann_seq OWNER TO postgres;

--
-- Name: anecdote_id_ann_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.anecdote_id_ann_seq OWNED BY public.anecdote.id_ann;


--
-- Name: articles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.articles (
    id_a integer NOT NULL,
    image_miniature character varying(150) NOT NULL,
    nom character varying(50) NOT NULL,
    date_creation timestamp without time zone NOT NULL,
    text text NOT NULL,
    tags character varying(50) NOT NULL,
    id_ann integer NOT NULL,
    id_u integer NOT NULL,
    text_eng text,
    tags_eng character varying(50),
    titre_eng character varying(50)
);


ALTER TABLE public.articles OWNER TO postgres;

--
-- Name: TABLE articles; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.articles IS 'Table des articles liÃ©s aux anecdotes et utilisateurs';


--
-- Name: COLUMN articles.id_a; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.articles.id_a IS 'Identifiant unique de l''article';


--
-- Name: COLUMN articles.date_creation; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.articles.date_creation IS 'Date et heure de crÃ©ation de l''article';


--
-- Name: COLUMN articles.text; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.articles.text IS 'Contenu textuel de l''article';


--
-- Name: COLUMN articles.id_ann; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.articles.id_ann IS 'RÃ©fÃ©rence vers l''anecdote associÃ©e';


--
-- Name: COLUMN articles.id_u; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.articles.id_u IS 'RÃ©fÃ©rence vers l''utilisateur auteur';


--
-- Name: articles_id_a_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.articles_id_a_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.articles_id_a_seq OWNER TO postgres;

--
-- Name: articles_id_a_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.articles_id_a_seq OWNED BY public.articles.id_a;


--
-- Name: comptes; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.comptes (
    id_compte integer NOT NULL,
    mail character varying(100) NOT NULL,
    mdp character varying(255) NOT NULL,
    pseudo character varying(50) NOT NULL,
    role character varying(20) NOT NULL,
    date_creation timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion timestamp without time zone,
    CONSTRAINT comptes_role_check CHECK (((role)::text = ANY (ARRAY[('gestionnaire'::character varying)::text, ('admin'::character varying)::text])))
);


ALTER TABLE public.comptes OWNER TO postgres;

--
-- Name: TABLE comptes; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.comptes IS 'Table des comptes utilisateurs pour l''authentification';


--
-- Name: COLUMN comptes.id_compte; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.id_compte IS 'Identifiant unique du compte';


--
-- Name: COLUMN comptes.mail; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.mail IS 'Adresse email de l''utilisateur (unique)';


--
-- Name: COLUMN comptes.mdp; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.mdp IS 'Mot de passe hach‚ de l''utilisateur';


--
-- Name: COLUMN comptes.pseudo; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.pseudo IS 'Nom d''utilisateur (unique)';


--
-- Name: COLUMN comptes.role; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.role IS 'R“le de l''utilisateur (gestionnaire ou admin)';


--
-- Name: COLUMN comptes.date_creation; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.date_creation IS 'Date de cr‚ation du compte';


--
-- Name: COLUMN comptes.derniere_connexion; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.comptes.derniere_connexion IS 'Date de la derniŠre connexion';


--
-- Name: comptes_id_compte_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.comptes_id_compte_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.comptes_id_compte_seq OWNER TO postgres;

--
-- Name: comptes_id_compte_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.comptes_id_compte_seq OWNED BY public.comptes.id_compte;


--
-- Name: utilisateurs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.utilisateurs (
    id_u integer NOT NULL,
    nom character varying(50) NOT NULL,
    role character varying(50) NOT NULL
);


ALTER TABLE public.utilisateurs OWNER TO postgres;

--
-- Name: TABLE utilisateurs; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.utilisateurs IS 'Table des utilisateurs du systÃ¨me';


--
-- Name: COLUMN utilisateurs.id_u; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.utilisateurs.id_u IS 'Identifiant unique de l''utilisateur';


--
-- Name: COLUMN utilisateurs.nom; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.utilisateurs.nom IS 'Nom de l''utilisateur';


--
-- Name: COLUMN utilisateurs.role; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.utilisateurs.role IS 'RÃ´le de l''utilisateur dans le systÃ¨me';


--
-- Name: utilisateurs_id_u_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.utilisateurs_id_u_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.utilisateurs_id_u_seq OWNER TO postgres;

--
-- Name: utilisateurs_id_u_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.utilisateurs_id_u_seq OWNED BY public.utilisateurs.id_u;


--
-- Name: anecdote id_ann; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anecdote ALTER COLUMN id_ann SET DEFAULT nextval('public.anecdote_id_ann_seq'::regclass);


--
-- Name: articles id_a; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.articles ALTER COLUMN id_a SET DEFAULT nextval('public.articles_id_a_seq'::regclass);


--
-- Name: comptes id_compte; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comptes ALTER COLUMN id_compte SET DEFAULT nextval('public.comptes_id_compte_seq'::regclass);


--
-- Name: utilisateurs id_u; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs ALTER COLUMN id_u SET DEFAULT nextval('public.utilisateurs_id_u_seq'::regclass);


--
-- Data for Name: anecdote; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.anecdote (id_ann, nom, text, date_, tags, text_eng, titre_eng, tags_eng) FROM stdin;
1	Les bouquinistes des quais	Les bouquinistes des quais de Seine sont une tradition parisienne depuis le 16Šme siŠcle. Ces vendeurs de livres d'occasion installent leurs boŒtes vertes le long des parapets et font partie int‚grante du paysage culturel parisien.	1850-01-01	Histoire, Culture	\N	\N	\N
2	Notre-Dame et les quais	La cath‚drale Notre-Dame de Paris domine majestueusement les quais de Seine depuis plus de 850 ans. Sa construction a commenc‚ en 1163 et elle reste l'un des monuments les plus visit‚s au monde.	1163-01-01	Histoire, Architecture	\N	\N	\N
3	Les p‚niches du quai de Jemmapes	Le canal Saint-Martin et ses quais abritent de nombreuses p‚niches transform‚es en habitations. Ces bateaux-logements offrent un mode de vie unique au cour de Paris.	1970-01-01	Activit‚s, Lifestyle	\N	\N	\N
4	Le pont des Arts et ses cadenas	Le pont des Arts ‚tait c‚lŠbre pour ses milliers de cadenas d'amour accroch‚s par les couples. Bien que retir‚s en 2015 pour des raisons de s‚curit‚, cette tradition reste dans les m‚moires.	2008-01-01	Culture, Romance	\N	\N	\N
5	Les crues de la Seine	Les quais de Seine ont ‚t‚ t‚moins de nombreuses crues historiques, notamment celle de 1910 qui reste la plus importante jamais enregistr‚e. Les marques de niveau d'eau sont encore visibles sur certains ponts.	1910-01-28	Histoire, Nature	\N	\N	\N
6	La voie Georges Pompidou	Cette voie rapide longe la rive droite de la Seine et fut inaugur‚e en 1967. Elle porte le nom du futur pr‚sident de la R‚publique et offre une perspective unique sur les monuments parisiens.	1967-01-01	Histoire, Transport	\N	\N	\N
7	Les guinguettes des bords de Seine	Au 19Šme siŠcle, les guinguettes ‚taient des lieux de divertissement populaires le long des quais. On y dansait, buvait et profitait des plaisirs simples au bord de l'eau.	1860-01-01	Histoire, Culture	\N	\N	\N
8	Les Bouquinistes Centenaires	En 1859, Napol‚on III officialise la pr‚sence des bouquinistes sur les quais de Seine. Ces marchands de livres d'occasion installent leurs boŒtes vertes le long des parapets, cr‚ant une tradition unique au monde. Aujourd'hui, on compte environ 240 bouquinistes r‚partis sur 3 kilomŠtres de quais.	1859-06-15	#histoire #culture #bouquinistes	\N	\N	\N
9	Le MystŠre du Vert-Galant	Le square du Vert-Galant, … la pointe de l'Œle de la Cit‚, tire son nom du surnom d'Henri IV. Une l‚gende raconte qu'un tunnel secret relierait ce square au Louvre, permettant au roi de rejoindre discrŠtement ses maŒtresses. Bien qu'aucune preuve n'existe, cette histoire fascine encore les Parisiens.	1600-05-14	#l‚gende #royaut‚ #architecture	\N	\N	\N
10	Les Crues M‚morables	En janvier 1910, la Seine sort de son lit et inonde Paris. Les quais disparaissent sous 8 mŠtres d'eau. Les Parisiens circulent en barque dans les rues. Cette crue centennale marque encore les m‚moires et des repŠres de niveau sont visibles sur les murs des quais.	1910-01-28	#catastrophe #climat #m‚moire	\N	\N	\N
11	L'Art des Ponts	Chaque pont de Paris raconte une histoire. Le Pont-Neuf, paradoxalement le plus ancien, le Pont Alexandre III et ses dorures, le moderne Pont de la Concorde... Les quais offrent le meilleur point de vue pour admirer cette architecture vari‚e qui enjambe la Seine depuis des siŠcles.	1578-07-31	#architecture #ponts #patrimoine	\N	\N	\N
12	Les Guinguettes d'Autrefois	Au XIXe siŠcle, les quais de Seine accueillaient de nombreuses guinguettes o— les Parisiens venaient danser et boire. Ces ‚tablissements populaires, immortalis‚s par Renoir et Toulouse-Lautrec, ont disparu mais leur souvenir plane encore sur les berges de la Seine.	1850-08-20	#loisirs #peinture #nostalgie	\N	\N	\N
13	Les Bouquinistes Centenaires	En 1859, Napoleon III officialise la presence des bouquinistes sur les quais de Seine. Ces marchands de livres d occasion installent leurs boites vertes le long des parapets, creant une tradition unique au monde. Aujourd hui, on compte environ 240 bouquinistes repartis sur 3 kilometres de quais.	1859-06-15	#histoire #culture #bouquinistes	\N	\N	\N
14	Le Mystere du Vert-Galant	Le square du Vert-Galant, a la pointe de l ile de la Cite, tire son nom du surnom d Henri IV. Une legende raconte qu un tunnel secret relierait ce square au Louvre, permettant au roi de rejoindre discretement ses maitresses. Bien qu aucune preuve n existe, cette histoire fascine encore les Parisiens.	1600-05-14	#legende #royaute #architecture	\N	\N	\N
15	Les Crues Memorables	En janvier 1910, la Seine sort de son lit et inonde Paris. Les quais disparaissent sous 8 metres d eau. Les Parisiens circulent en barque dans les rues. Cette crue centennale marque encore les memoires et des reperes de niveau sont visibles sur les murs des quais.	1910-01-28	#catastrophe #climat #memoire	\N	\N	\N
16	L Art des Ponts	Chaque pont de Paris raconte une histoire. Le Pont-Neuf, paradoxalement le plus ancien, le Pont Alexandre III et ses dorures, le moderne Pont de la Concorde... Les quais offrent le meilleur point de vue pour admirer cette architecture variee qui enjambe la Seine depuis des siecles.	1578-07-31	#architecture #ponts #patrimoine	\N	\N	\N
17	Les Guinguettes d Autrefois	Au XIXe siecle, les quais de Seine accueillaient de nombreuses guinguettes ou les Parisiens venaient danser et boire. Ces etablissements populaires, immortalises par Renoir et Toulouse-Lautrec, ont disparu mais leur souvenir plane encore sur les berges de la Seine.	1850-08-20	#loisirs #peinture #nostalgie	\N	\N	\N
18	Bouquinistes Centenaires	En 1859, Napoleon III officialise la presence des bouquinistes sur les quais de Seine. Ces marchands de livres d occasion installent leurs boites vertes le long des parapets, creant une tradition unique au monde. Aujourd hui, on compte environ 240 bouquinistes repartis sur 3 kilometres de quais.	1859-06-15	#histoire #culture	\N	\N	\N
19	Mystere du Vert-Galant	Le square du Vert-Galant, a la pointe de l ile de la Cite, tire son nom du surnom d Henri IV. Une legende raconte qu un tunnel secret relierait ce square au Louvre, permettant au roi de rejoindre discretement ses maitresses. Bien qu aucune preuve n existe, cette histoire fascine encore les Parisiens.	1600-05-14	#legende #royaute	\N	\N	\N
20	Crues Memorables	En janvier 1910, la Seine sort de son lit et inonde Paris. Les quais disparaissent sous 8 metres d eau. Les Parisiens circulent en barque dans les rues. Cette crue centennale marque encore les memoires et des reperes de niveau sont visibles sur les murs des quais.	1910-01-28	#catastrophe #climat	\N	\N	\N
21	Art des Ponts	Chaque pont de Paris raconte une histoire. Le Pont-Neuf, paradoxalement le plus ancien, le Pont Alexandre III et ses dorures, le moderne Pont de la Concorde... Les quais offrent le meilleur point de vue pour admirer cette architecture variee qui enjambe la Seine depuis des siecles.	1578-07-31	#architecture #ponts	\N	\N	\N
22	Guinguettes d Autrefois	Au XIXe siecle, les quais de Seine accueillaient de nombreuses guinguettes ou les Parisiens venaient danser et boire. Ces etablissements populaires, immortalises par Renoir et Toulouse-Lautrec, ont disparu mais leur souvenir plane encore sur les berges de la Seine.	1850-08-20	#loisirs #peinture	\N	\N	\N
23	Bouquinistes Centenaires	En 1859, Napoleon III officialise la presence des bouquinistes sur les quais de Seine. Ces marchands de livres d occasion installent leurs boites vertes le long des parapets, creant une tradition unique au monde. Aujourd hui, on compte environ 240 bouquinistes repartis sur 3 kilometres de quais.	1859-06-15	#histoire #culture	\N	\N	\N
24	Mystere du Vert-Galant	Le square du Vert-Galant, a la pointe de l ile de la Cite, tire son nom du surnom d Henri IV. Une legende raconte qu un tunnel secret relierait ce square au Louvre, permettant au roi de rejoindre discretement ses maitresses. Bien qu aucune preuve n existe, cette histoire fascine encore les Parisiens.	1600-05-14	#legende #royaute	\N	\N	\N
25	Crues Memorables	En janvier 1910, la Seine sort de son lit et inonde Paris. Les quais disparaissent sous 8 metres d eau. Les Parisiens circulent en barque dans les rues. Cette crue centennale marque encore les memoires et des reperes de niveau sont visibles sur les murs des quais.	1910-01-28	#catastrophe #climat	\N	\N	\N
26	Art des Ponts	Chaque pont de Paris raconte une histoire. Le Pont-Neuf, paradoxalement le plus ancien, le Pont Alexandre III et ses dorures, le moderne Pont de la Concorde... Les quais offrent le meilleur point de vue pour admirer cette architecture variee qui enjambe la Seine depuis des siecles.	1578-07-31	#architecture #ponts	\N	\N	\N
27	Guinguettes d Autrefois	Au XIXe siecle, les quais de Seine accueillaient de nombreuses guinguettes ou les Parisiens venaient danser et boire. Ces etablissements populaires, immortalises par Renoir et Toulouse-Lautrec, ont disparu mais leur souvenir plane encore sur les berges de la Seine.	1850-08-20	#loisirs #peinture	\N	\N	\N
\.


--
-- Data for Name: articles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.articles (id_a, image_miniature, nom, date_creation, text, tags, id_ann, id_u, text_eng, tags_eng, titre_eng) FROM stdin;
5	https://parisjetaime.com/data/layout_image/22359_Jardins-de-l%27Archipel-des-Berges-de-Seine-Niki-de-Saint-Phalle--630x405--%C2%A9-Marc-Bertrand.jpg	La Grande Crue de 1910	2024-02-18 11:00:00	En janvier 1910, la Seine envahit Paris. Les quais disparaissent sous 8 metres d'eau. Cette crue historique transforme la capitale en Venise ephemere. Les reperes de niveau, encore visibles, temoignent de cet evenement exceptionnel.	histoire, catastrophe, memoire	5	1	\N	\N	\N
4	https://uploads.lebonbon.fr/source/2024/march/2057908/quais-de-seine-paris_1_2000.jpg	Le Pont des Arts et ses Cadenas	2024-02-10 16:20:00	Pendant des annees, les cadenas d'amour ornaient le Pont des Arts. Couples du monde entier y gravaient leurs serments. Cette tradition romantique, bien que supprimee, reste gravee dans la memoire collective parisienne.	romance, tradition, culture	4	5	\N	\N	\N
3	https://cms.outtrip.fr/assets/outtrip/_1200x630_crop_center-center_82_none/Courir-sur-les-quais-de-Seine-%C3%A0-Paris.png?mtime=1628517472	Vivre sur une Peniche	2024-02-03 09:45:00	Les peniches transformees en habitations offrent un mode de vie unique. Amarrees le long des quais, elles allient confort moderne et charme nautique. Leurs proprietaires ont choisi de vivre au rythme de la Seine.	lifestyle, habitat, originalite	3	3	\N	\N	\N
2	https://www.lesbarres.com/blog/wp-content/uploads/2017/11/bar-peniche-quais-de-seine-paris.jpg	Notre-Dame vue des Quais	2024-01-20 14:15:00	La cathedrale gothique domine majestueusement les quais depuis 850 ans. Sa silhouette se reflete dans les eaux calmes de la Seine. Les quais offrent la plus belle perspective pour admirer ce chef-d'oeuvre architectural.	architecture, histoire, patrimoine	2	4	\N	\N	\N
1	https://cms.outtrip.fr/assets/outtrip/_1200x630_crop_center-center_82_none/Courir-sur-les-quais-de-Seine-%C3%A0-Paris.png?mtime=1628517472	Les Bouquinistes Parisiens	2024-01-15 10:30:00	Depuis 1859, les bouquinistes animent les quais de Seine avec leurs boites vertes. Ces marchands de livres anciens perpetuent une tradition unique au monde. Chaque jour, ils ouvrent leurs etals face a Notre-Dame, offrant aux passants des tresors litteraires.	histoire, culture, livres	2	1	\N	\N	\N
7	guinguettes_seine.jpg	Les Guinguettes d'Antan	2024-03-05 13:45:00	Au 19e siecle, les guinguettes animaient les bords de Seine. Ouvriers et bourgeois s'y retrouvaient pour danser et boire. Ces etablissements populaires, immortalises par les peintres impressionnistes, incarnaient la joie de vivre parisienne.	loisirs, histoire, culture	7	5	In the 19th century, guinguettes animated the banks of the Seine. Workers and bourgeois gathered there to dance and drink. These popular establishments, immortalized by Impressionist painters, embodied the Parisian joie de vivre.	\N	\N
10	https://www.splendia.com/wp-content/uploads/2024/01/Quais-de-Seine1-1024x683.jpg	Promenade sur les Quais	2024-03-28 17:30:00	Une promenade sur les quais de Seine traverse l'histoire de Paris. Chaque pierre, chaque parapet raconte une anecdote. Du Pont-Neuf a l'ile Saint-Louis, ces chemins pietons offrent une evasion unique au coeur de la capitale.	tourisme, patrimoine, decouverte	8	3	A walk along the Seine quays crosses through Paris history. Every stone, every parapet tells an anecdote. From Pont-Neuf to ×le Saint-Louis, these pedestrian paths offer a unique escape in the heart of the capital.	\N	\N
8	https://www.pariszigzag.fr/wp-content/uploads/2020/06/shutterstock_495019168-e1625217963918.jpg	Le Secret du Vert-Galant	2024-03-12 15:20:00	Le square du Vert-Galant cache-t-il un tunnel secret? La legende raconte qu'Henri IV rejoignait discretement ses favorites par un passage souterrain. Cette histoire, vraie ou fausse, ajoute au mystere de ce lieu emblematique.	legende, royaute, mystere	9	2	Does the Vert-Galant square hide a secret tunnel? Legend tells that Henri IV discreetly joined his favorites through an underground passage. This story, true or false, adds to the mystery of this emblematic place.	\N	\N
6	https://cdn.sortiraparis.com/images/80/83517/584120-visuel-paris-quai-de-seine.jpg	La Voie Georges Pompidou	2024-02-25 08:30:00	Inauguree en 1967, cette voie express longe la rive droite. Elle porte le nom du futur president et offre une perspective dynamique sur les monuments. Les automobilistes beneficient d'un panorama unique sur Paris historique.	transport, urbanisme, patrimoine	6	2	Inaugurated in 1967, this expressway runs along the right bank. It bears the name of the future president and offers a dynamic perspective on the monuments. Motorists benefit from a unique panorama of historic Paris.	\N	\N
\.


--
-- Data for Name: comptes; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.comptes (id_compte, mail, mdp, pseudo, role, date_creation, derniere_connexion) FROM stdin;
1	admin@quaisdeseine.fr	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	admin	admin	2025-06-08 16:32:06.380716	\N
2	gestionnaire1@quaisdeseine.fr	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	gestionnaire1	gestionnaire	2025-06-08 16:32:06.380716	\N
3	gestionnaire2@quaisdeseine.fr	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	gestionnaire2	gestionnaire	2025-06-08 16:32:06.380716	\N
4	contact@tayoken.xyz	$2y$10$zhLd4sturCuOT3IUWksswuOSzqbRlnMMEFxH0nOuB6TTzO9iCV.4i	tayoken	admin	2025-06-08 16:39:15.301843	2025-06-10 08:54:26.504229
\.


--
-- Data for Name: utilisateurs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.utilisateurs (id_u, nom, role) FROM stdin;
1	Marie Dubois	Historienne
2	Pierre Martin	Guide touristique
3	Sophie Laurent	Photographe
4	Jean Rousseau	Architecte
5	Claire Moreau	Journaliste culturelle
\.


--
-- Name: anecdote_id_ann_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.anecdote_id_ann_seq', 27, true);


--
-- Name: articles_id_a_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.articles_id_a_seq', 11, true);


--
-- Name: comptes_id_compte_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.comptes_id_compte_seq', 4, true);


--
-- Name: utilisateurs_id_u_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.utilisateurs_id_u_seq', 5, true);


--
-- Name: anecdote anecdote_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.anecdote
    ADD CONSTRAINT anecdote_pkey PRIMARY KEY (id_ann);


--
-- Name: articles articles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT articles_pkey PRIMARY KEY (id_a);


--
-- Name: comptes comptes_mail_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comptes
    ADD CONSTRAINT comptes_mail_key UNIQUE (mail);


--
-- Name: comptes comptes_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comptes
    ADD CONSTRAINT comptes_pkey PRIMARY KEY (id_compte);


--
-- Name: comptes comptes_pseudo_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.comptes
    ADD CONSTRAINT comptes_pseudo_key UNIQUE (pseudo);


--
-- Name: utilisateurs utilisateurs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.utilisateurs
    ADD CONSTRAINT utilisateurs_pkey PRIMARY KEY (id_u);


--
-- Name: idx_anecdote_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_anecdote_date ON public.anecdote USING btree (date_);


--
-- Name: idx_articles_date_creation; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_articles_date_creation ON public.articles USING btree (date_creation);


--
-- Name: idx_articles_id_ann; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_articles_id_ann ON public.articles USING btree (id_ann);


--
-- Name: idx_articles_id_u; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_articles_id_u ON public.articles USING btree (id_u);


--
-- Name: idx_comptes_mail; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_comptes_mail ON public.comptes USING btree (mail);


--
-- Name: idx_comptes_pseudo; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_comptes_pseudo ON public.comptes USING btree (pseudo);


--
-- Name: idx_comptes_role; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_comptes_role ON public.comptes USING btree (role);


--
-- Name: articles fk_articles_anecdote; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT fk_articles_anecdote FOREIGN KEY (id_ann) REFERENCES public.anecdote(id_ann);


--
-- Name: articles fk_articles_utilisateur; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.articles
    ADD CONSTRAINT fk_articles_utilisateur FOREIGN KEY (id_u) REFERENCES public.utilisateurs(id_u);


--
-- PostgreSQL database dump complete
--

