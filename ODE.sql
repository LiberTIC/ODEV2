PGDMP                         s           ODE    9.4.2    9.4.0 *    
	           0    0    ENCODING    ENCODING        SET client_encoding = 'UTF8';
                       false            	           0    0 
   STDSTRINGS 
   STDSTRINGS     (   SET standard_conforming_strings = 'on';
                       false            	           1262    16393    ODE    DATABASE     w   CREATE DATABASE "ODE" WITH TEMPLATE = template0 ENCODING = 'UTF8' LC_COLLATE = 'en_US.UTF-8' LC_CTYPE = 'en_US.UTF-8';
    DROP DATABASE "ODE";
             ODE    false                        2615    2200    public    SCHEMA        CREATE SCHEMA public;
    DROP SCHEMA public;
             thibaud    false            	           0    0    SCHEMA public    COMMENT     6   COMMENT ON SCHEMA public IS 'standard public schema';
                  thibaud    false    5            	           0    0    public    ACL     �   REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM thibaud;
GRANT ALL ON SCHEMA public TO thibaud;
GRANT ALL ON SCHEMA public TO PUBLIC;
                  thibaud    false    5            �            3079    12123    plpgsql 	   EXTENSION     ?   CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;
    DROP EXTENSION plpgsql;
                  false            	           0    0    EXTENSION plpgsql    COMMENT     @   COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';
                       false    181            �            1259    16396    calendar    TABLE       CREATE TABLE calendar (
    uid integer NOT NULL,
    principaluri text,
    displayname text,
    uri text,
    synctoken integer,
    description text,
    calendarorder integer,
    calendarcolor text,
    timezone text,
    components text[],
    transparent integer
);
    DROP TABLE public.calendar;
       public         ODE    false    5            �            1259    16424    calendarchange    TABLE     �   CREATE TABLE calendarchange (
    id integer NOT NULL,
    uri text,
    synctoken integer,
    calendarid integer,
    operation integer
);
 "   DROP TABLE public.calendarchange;
       public         ODE    false    5            �            1259    16422    calendarchanges_id_seq    SEQUENCE     x   CREATE SEQUENCE calendarchanges_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 -   DROP SEQUENCE public.calendarchanges_id_seq;
       public       ODE    false    176    5            	           0    0    calendarchanges_id_seq    SEQUENCE OWNED BY     B   ALTER SEQUENCE calendarchanges_id_seq OWNED BY calendarchange.id;
            public       ODE    false    175            �            1259    16407    calendarobject    TABLE     �   CREATE TABLE calendarobject (
    uri text,
    lastmodified integer,
    calendarid integer,
    calendardata text,
    etag text,
    size integer,
    extracted_data json,
    uid text NOT NULL,
    component text
);
 "   DROP TABLE public.calendarobject;
       public         ODE    false    5            �            1259    16394    calendars_uid_seq    SEQUENCE     s   CREATE SEQUENCE calendars_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 (   DROP SEQUENCE public.calendars_uid_seq;
       public       ODE    false    173    5            	           0    0    calendars_uid_seq    SEQUENCE OWNED BY     8   ALTER SEQUENCE calendars_uid_seq OWNED BY calendar.uid;
            public       ODE    false    172            �            1259    16461 	   principal    TABLE     {   CREATE TABLE principal (
    id integer NOT NULL,
    uri text,
    email text,
    displayname text,
    vcardurl text
);
    DROP TABLE public.principal;
       public         ODE    false    5            �            1259    16459    principal_id_seq    SEQUENCE     r   CREATE SEQUENCE principal_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 '   DROP SEQUENCE public.principal_id_seq;
       public       ODE    false    180    5            	           0    0    principal_id_seq    SEQUENCE OWNED BY     7   ALTER SEQUENCE principal_id_seq OWNED BY principal.id;
            public       ODE    false    179            �            1259    16446    users    TABLE       CREATE TABLE users (
    id integer NOT NULL,
    username text,
    username_canonical text,
    email text,
    email_canonical text,
    salt text,
    password text,
    password_digesta text,
    locked boolean DEFAULT false,
    expired boolean DEFAULT false,
    expires_at text,
    confirmation_token text,
    password_requested_at text,
    roles text[],
    credentials_expired boolean DEFAULT false,
    credentials_expire_at text,
    enabled boolean DEFAULT true,
    last_login timestamp without time zone
);
    DROP TABLE public.users;
       public         ODE    false    5            �            1259    16444    user_uid_seq    SEQUENCE     n   CREATE SEQUENCE user_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;
 #   DROP SEQUENCE public.user_uid_seq;
       public       ODE    false    178    5            	           0    0    user_uid_seq    SEQUENCE OWNED BY     /   ALTER SEQUENCE user_uid_seq OWNED BY users.id;
            public       ODE    false    177            ~           2604    16399    uid    DEFAULT     _   ALTER TABLE ONLY calendar ALTER COLUMN uid SET DEFAULT nextval('calendars_uid_seq'::regclass);
 ;   ALTER TABLE public.calendar ALTER COLUMN uid DROP DEFAULT;
       public       ODE    false    173    172    173                       2604    16427    id    DEFAULT     i   ALTER TABLE ONLY calendarchange ALTER COLUMN id SET DEFAULT nextval('calendarchanges_id_seq'::regclass);
 @   ALTER TABLE public.calendarchange ALTER COLUMN id DROP DEFAULT;
       public       ODE    false    175    176    176            �           2604    16464    id    DEFAULT     ^   ALTER TABLE ONLY principal ALTER COLUMN id SET DEFAULT nextval('principal_id_seq'::regclass);
 ;   ALTER TABLE public.principal ALTER COLUMN id DROP DEFAULT;
       public       ODE    false    179    180    180            �           2604    16449    id    DEFAULT     V   ALTER TABLE ONLY users ALTER COLUMN id SET DEFAULT nextval('user_uid_seq'::regclass);
 7   ALTER TABLE public.users ALTER COLUMN id DROP DEFAULT;
       public       ODE    false    177    178    178             	          0    16396    calendar 
   TABLE DATA               �   COPY calendar (uid, principaluri, displayname, uri, synctoken, description, calendarorder, calendarcolor, timezone, components, transparent) FROM stdin;
    public       ODE    false    173   =,       	          0    16424    calendarchange 
   TABLE DATA               L   COPY calendarchange (id, uri, synctoken, calendarid, operation) FROM stdin;
    public       ODE    false    176   .       	           0    0    calendarchanges_id_seq    SEQUENCE SET     >   SELECT pg_catalog.setval('calendarchanges_id_seq', 64, true);
            public       ODE    false    175            	          0    16407    calendarobject 
   TABLE DATA               z   COPY calendarobject (uri, lastmodified, calendarid, calendardata, etag, size, extracted_data, uid, component) FROM stdin;
    public       ODE    false    174   �0       	           0    0    calendars_uid_seq    SEQUENCE SET     9   SELECT pg_catalog.setval('calendars_uid_seq', 14, true);
            public       ODE    false    172            	          0    16461 	   principal 
   TABLE DATA               C   COPY principal (id, uri, email, displayname, vcardurl) FROM stdin;
    public       ODE    false    180   �5       	           0    0    principal_id_seq    SEQUENCE SET     7   SELECT pg_catalog.setval('principal_id_seq', 6, true);
            public       ODE    false    179            	           0    0    user_uid_seq    SEQUENCE SET     3   SELECT pg_catalog.setval('user_uid_seq', 4, true);
            public       ODE    false    177            	          0    16446    users 
   TABLE DATA                 COPY users (id, username, username_canonical, email, email_canonical, salt, password, password_digesta, locked, expired, expires_at, confirmation_token, password_requested_at, roles, credentials_expired, credentials_expire_at, enabled, last_login) FROM stdin;
    public       ODE    false    178   26       �           2606    16404    calendar_pkey 
   CONSTRAINT     N   ALTER TABLE ONLY calendar
    ADD CONSTRAINT calendar_pkey PRIMARY KEY (uid);
 @   ALTER TABLE ONLY public.calendar DROP CONSTRAINT calendar_pkey;
       public         ODE    false    173    173            �           2606    16432    calendarchanges_pkey 
   CONSTRAINT     Z   ALTER TABLE ONLY calendarchange
    ADD CONSTRAINT calendarchanges_pkey PRIMARY KEY (id);
 M   ALTER TABLE ONLY public.calendarchange DROP CONSTRAINT calendarchanges_pkey;
       public         ODE    false    176    176            �           2606    16415    calendarobject_pkey 
   CONSTRAINT     Z   ALTER TABLE ONLY calendarobject
    ADD CONSTRAINT calendarobject_pkey PRIMARY KEY (uid);
 L   ALTER TABLE ONLY public.calendarobject DROP CONSTRAINT calendarobject_pkey;
       public         ODE    false    174    174            �           2606    16455 	   user_pkey 
   CONSTRAINT     F   ALTER TABLE ONLY users
    ADD CONSTRAINT user_pkey PRIMARY KEY (id);
 9   ALTER TABLE ONLY public.users DROP CONSTRAINT user_pkey;
       public         ODE    false    178    178            �           1259    16443    fki_cal_fkey    INDEX     F   CREATE INDEX fki_cal_fkey ON calendarchange USING btree (calendarid);
     DROP INDEX public.fki_cal_fkey;
       public         ODE    false    176            �           1259    16421    fki_calendar_fkey    INDEX     K   CREATE INDEX fki_calendar_fkey ON calendarobject USING btree (calendarid);
 %   DROP INDEX public.fki_calendar_fkey;
       public         ODE    false    174            �           2606    16438    cal_fkey    FK CONSTRAINT     o   ALTER TABLE ONLY calendarchange
    ADD CONSTRAINT cal_fkey FOREIGN KEY (calendarid) REFERENCES calendar(uid);
 A   ALTER TABLE ONLY public.calendarchange DROP CONSTRAINT cal_fkey;
       public       ODE    false    2183    176    173            �           2606    16416    calendar_fkey    FK CONSTRAINT     t   ALTER TABLE ONLY calendarobject
    ADD CONSTRAINT calendar_fkey FOREIGN KEY (calendarid) REFERENCES calendar(uid);
 F   ALTER TABLE ONLY public.calendarobject DROP CONSTRAINT calendar_fkey;
       public       ODE    false    2183    173    174             	   �  x��T�n�@<��b�S`l0D>�8H�M�5�-_��b�T�ݻ@�`%U�� f�};Z
������/���T5�uQ�=$���@11��h#C(��KY�۪l�����S ^r��Uw��=L���zMB��xl%N�;��8��:c"�x��E� 
�0ܦ��0��0⼀<��#��W���A�|����
��"rG�m��bv�	(yT��g{mJ�%o��C��]�}����a�d(x�<"��HB�ՈP��s����b���Uo�������d*]!l�1�Ė� 4��c����K���GI��I�vrg
�,�]_$�
��_�&FĒ�~�J�?���f�#1?^�+cK�L��h�����e��%\�cm�n�v��ꕅ�k��^�{�z��|��y�����SjϣA��ldcm����F!�(���*��J�)< fbϾ��?.�P���X,� c       	   �  x����� �gλP�+xOѭS羿T'>�:�E|��Ɓ� I�'��Ug�F�˅�C�r+FfU�e������GR�ɢ���V���Ǥ�y�Y����&�`ϥ�{�^���+]d�ǀ���{��ڝ��R����6$H��6���7zo����}@��Ae����^ �v�<d˘Z���7c�)=�8����c����a��-��������lu+�o�5�g��aOͰ(��ͪ�;�fA(���Hh���P�7��	�a;@B�xPZ=�ImC��r!��wh�����VF�!�g�B��3���� �_��Z�G�$N0�״iwц�������1��UMW�Rd_S)��Y4���x�d����*vU�V9�x���6pI� ��coV饃�@֝�=fC�U
)�U����fLƝ�o��/j�r�教*�]\�@Z t��.���PH��%�z�m�|	xQL,��Y�dl��w��/��~���tM^�8�{�ѫ&q��޼�}��'o�����PZ�VE���\��ݷ�^�6V�殨����|�[M� �@4�F�kZV�Vf���
��UZ�c���x���}�Z�g�'���[��I8����*k���Cx�� �G]� �@�=�1�JH��( ���?>��_�vn      	   �  x��Vێ�F}�|��b��f�R0��&����Ѩ��13H���n����2?�j0��x��ȶh�NWU���@3u�ES#QW$otCTL�od{tc��8*:��iCC�FG�Э;��{�fc�[�Uz�<ߝ�,�'�ׅ7�cK�$�����<|$����$	�8��ܔu�۹�ڕt�ݡY�ߗ`���cǁ�Ӆ�ʊ!��(}����_N�������S���x�"��%q�	�.�Y���xT���deJ���\��{T�,'҈�$NϜ�\�!O-�`7X��R�ٛ\�ٓ%z��\+��#ab�&���'�2�mF��I.����u�C,X5��:��i�J�٨k�����{7�;ҵ�u������ �5)"�,�R��$f�!�_���1ǃ��`�2P�JT�&������ �/%M`u��ժ�<[E�G���2z�j����@(����8�]8`BC2���P�:0u��6�^*%P���gC�diD(�D��	�Y��zĂ����7"�,e1�{R�4�	,p���F	���E��$B5�B~i@�e�V�h��oBZ��� K/�����r��\]��k-PeMn7�z @��N�(?��^�їoYR~^��	3�2R����1�f����V���$�2�$:��/���^؎|����Zo<`�|�XH�$.�0!a����ҫV��,��)h�3�r�כ��q4�t ϲ�"�b�g]8=o�i��m֞���ۖ�ܒ����^�P��e����k�� ���de/$���;谏��]�,�2]GIV�{Q��
���� g�f��� ��ؖ�!�P1�*��l�8�=Q)��ϻ,=)d/��@��Sd��m!O��m���v'�# zG��#.<8	j����x���x� �ȯ5Ώ�d졙�m�
S��f_5��@芮��Ќ�p`�C}���e�*���'4WG����:*Y�JY&���"���gO d��8F} ��2N��;WT����@�dQ5��4�l$��7�А�龜�(.ꔼ��H(k�;"��(f��Zf�#���P�sB��()���:@�"����� ��eЩ >'����;�e�����-�n����]���:��/�zdkxC�<!�s
:�.h���C��G���g
X��٢�+�h�2��K�L�=���<}�#��qT�j�ƴjH� �d]F��҃0��ɚ^sŀ�?mJ���Y������f�j      	   x   x�3�,(��K�,H�)�OL�����`R/���,��e��T?91'5/%�H��(��R�(51�vc��ˋ2KRq�7A�_�Z\�	"@�^r~.gH��]VWb�kFH/ĉ�5��qqq w�vm      	   U  x�]�[O�0 F��_�;����31�!Q�DDFxٺ�@;���$��.�%ߗ���C�Yi��~��D���̎S�mv�t����!�	��cd�j������A<�'8ݻD糙[�2:^��fur�тlGiF��j.�d3���v�P�������<\�F��2�0(� � "��i����޿͝�#/+���/�_������
lyC�)Ε�Lrf�W�^nƭ�B����[Qo)��OX<��Mm�6�ᚪ[	��u6l�U/^O���'ݺ��$����H��&;�|�1*
DIN�XVP�DEƊB��}���! �6�\@z��%�����`��     