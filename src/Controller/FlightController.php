<?php

namespace App\Controller;

use App\Entity\Flight;
use App\Entity\Messages;
use App\Form\FlightType;
use App\Model\NotificationModel;
use App\Repository\FlightOrderRepository;
use App\Repository\FlightRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api/{version<\d+>?1}/flight", defaults={"version"=1}, requirements={"version"="\d+"})
 *
 * @SWG\Parameter(
 *     name="version",
 *     in="path",
 *     type="integer",
 *     default="1"
 * )
 * Class FlightController
 * @package App\Controller
 */
class FlightController extends AbstractController
{
    /**
     * @SWG\Tag(name="Flight")
     *
     * @SWG\Parameter(
     *         name="page",
     *         in="query",
     *         default="1",
     *         required=true,
     *         type="integer"
     *     )
     * @SWG\Parameter(
     *         name="limit",
     *         in="query",
     *         default="10",
     *         required=false,
     *         type="integer"
     *     )
     *
     * @SWG\Response(
     *     response=200,
     *     description="This method is used to get all avalibel flights",
     *     @SWG\Schema(
     *          @SWG\Property(
     *          property="items",
     *          type="array",
     *              @SWG\Items(ref=@Model(type=Flight::class, groups={"flight:read",
     *                  "flight:from:address", "flight:to:address", "address:read"}))),
     *              @SWG\Property(
     *                  property="resultCount",
     *                  type="integer"
     *              ),
     *              @SWG\Property(
     *                  property="totalResultCount",
     *                  type="integer"
     *               ),
     *              @SWG\Property(
     *                  property="page",
     *                  type="integer"
     *               ),
     *              @SWG\Property(
     *                  property="limit",
     *                  type="integer"
     *              ),
     *     )
     * )
     *
     * @Route("/", name="flight-list", methods={"GET"})
     */
    public function index(Request $request, FlightRepository $repository): Response
    {

        $em = $this->getDoctrine()->getManager();
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);

        $query = $em->getRepository(Flight::class)->findActual();

        $paginator = new Paginator($query,  $fetchJoinCollection = true);

        $paginator->getQuery()

            ->setFirstResult($limit*($page-1))
            ->setMaxResults($limit)
        ;

        $result = [
            'items'=>$paginator->getIterator(),
            'resultCount'=>$paginator->getIterator()->count(),
            'totalResultCount'=>$paginator->count(),
            'page'=>$page,
            'limit'=>$limit,
        ];


        return $this->json($result, Response::HTTP_OK, [], ['groups' => Flight::FLIGHT_LIST]);
    }

    /**
     * @SWG\Tag(name="Flight")
     *
     * @SWG\Parameter(
     *         name="Flight",
     *         in="body",
     *         description="Inserted Flight object.",
     *         required=false,
     *         type="object",
     *          @Model(type=Flight::class, groups={"flight:add", "address:add", "object-name"})
     *      ),
     *
     * @SWG\Response(
     *     response=200,
     *     description="This method is used to add new flight.",
     *     @SWG\Schema(
     *     type="object",
     *         ref=@Model(type=Flight::class, groups={"flight:read", "flight:from:address", "flight:to:address", "address:read"})
     *      )
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="This method is used to add new flight.",
     *     @SWG\Schema(
     *
     *      @SWG\Property(
     *         property="messages",
     *         type="array",
     *         @SWG\Items(
     *             type="object",
     *             @SWG\Property(property="propertyName", type="string"))
     *           ),
     *       )
     *     )
     * )
     *
     * @Route("/add", name="flight-add", methods={"POST"})
     */
    public function add(Request $request, ValidatorInterface $validator): Response
    {

        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);

        $fl = new Flight();
        $form = $this->createForm(FlightType::class, $fl, ['csrf_protection' => false]);

        $form->submit($data);

        $errors = $validator->validate($fl, null, ['add-flight', 'address-add']);
        $errorMessages = [];

        if(count($errors) >0) {
            foreach ($errors as $error) {

                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['messages'=>$errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $em->persist($fl);

        try {

            $em->flush();

        }catch (\Exception $e) {

            return $this->json($e->getMessage());
        }

        return $this->json($fl, Response::HTTP_OK, [], ['groups' => Flight::FLIGHT_LIST]);
    }

    /**
     * @SWG\Tag(name="Flight")
     *
     *
     * @SWG\Response(
     *     response="204",
     *     description="The Flight Tcket Sales Completed."
     *
     *     )
     *
     * @Route("/finish/{id}", name="flight-finish", methods={"GET"})
     */
    public function finishFlight(int $id, Request $request, HubInterface $hub, SerializerInterface $serializer) {

        $em = $this->getDoctrine()->getManager();

        $flight = $em->getRepository(Flight::class)->find($id);

        $flight->setStatus(Flight::IS_CLOSED);

        $em->persist($flight);
        $em->flush();

        $notificationModel = new NotificationModel($flight);

        $messageSerialized = $serializer->serialize(['data'=>$notificationModel->toArray()], 'json');

        $update = new Update(
            'api/v1/callback/events',
            $messageSerialized
        );

        $hub->publish($update);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     *
     *
     * @SWG\Tag(name="Flight")
     *
     * @SWG\Response(
     *     response="204",
     *     description="The Flight Cancled."
     *
     *     )
     *
     * @Route("/cancel/{id}", name="flight-cancel", methods={"GET"})
     */
    public function cancelFlight(int $id, Request $request, HubInterface $hub, SerializerInterface $serializer) {

        $em = $this->getDoctrine()->getManager();

        $flight = $em->getRepository(Flight::class)->find($id);

        $flight->setStatus(Flight::IS_CANCELED);

        $em->persist($flight);
        $em->flush();

        $notificationModel = new NotificationModel($flight);

        $messageSerialized = $serializer->serialize(['data'=>$notificationModel->toArray()], 'json');

        $update = new Update(
            'api/v1/callback/events',
            $messageSerialized
        );

        $hub->publish($update);

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
